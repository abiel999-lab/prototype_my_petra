<?php

namespace App\Http\Controllers\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Services\LoggingService;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Ticketing;
use Illuminate\Support\Str;
use App\Services\WhatsAppService;


class SupportController extends Controller
{
    public function index()
    {
        return view('auth.customer-support'); // Menampilkan halaman Customer Support
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'issue_type' => 'required|string',
            'message' => 'required|string',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $isLogin = $request->issue_type === 'login problem';

        if ($isLogin) {
            $request->validate([
                'phone_number' => 'required|string',
                'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);
        } else {
            $request->validate([
                'email' => 'required|email',
            ]);

            // Cek email di database (hanya jika bukan login problem)
            if (!\App\Models\User::where('email', $request->email)->exists()) {
                return back()->withErrors(['email' => 'Email is not registered in the system.']);
            }

            // Cegah spam (ganda di hari yang sama)
            if (
                Ticketing::where('email', $request->email)
                    ->where('issue_type', $request->issue_type)
                    ->whereDate('created_at', Carbon::today())->exists()
            ) {
                return back()->withErrors(['message' => 'You have already submitted a similar issue today.']);
            }
        }

        // ✅ RATE LIMIT MANUAL: Maks 2 per hari (email atau phone_number)
        $ip = $request->ip();

        $ticketCount = Ticketing::where('ip_address', $ip)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($ticketCount >= 2) {
            return back()->withErrors([
                'message' => 'You have reached the limit of 2 support requests today. Try again after the previous ticket completed.',
            ]);
        }



        $supportEmail = env('MAIL_USERNAME', 'mfa.mypetra@petra.ac.id'); // Default email jika MAIL_USERNAME tidak diatur
        $submittedAt = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB';


        try {
            $ticketCode = strtoupper(Str::random(6)); // ex: A7B3KC
            $attachmentPath = null;

            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('support_attachments', 'public');
            }

            Ticketing::create([
                'ticket_code' => $ticketCode,
                'name' => $request->name,
                'email' => data_get($request, 'email'),
                'phone_number' => data_get($request, 'phone_number'),
                'issue_type' => $request->issue_type,
                'message' => $request->message,
                'attachment' => $attachmentPath,
                'ip_address' => $request->ip(),
            ]);

            // Kirim email/whatsapp ke user
            $emailEmpty = empty($request->email) || $request->email === '-';

            if ($emailEmpty && !empty($request->phone_number)) {
                $wa = new WhatsAppService();
                $wa->sendSupportTicket($request->phone_number, $ticketCode, $request->issue_type, $request->name);
            } elseif (!$emailEmpty) {
                Mail::send('emails.ticket-confirmation', [
                    'name'         => $request->name,
                    'ticket_code'  => $ticketCode,
                    'issue_type'   => $request->issue_type,
                    'submitted_at' => $submittedAt,
                ], function ($message) use ($request, $ticketCode) {
                    $message->to($request->email)
                            ->subject("Ticket Confirmation [{$ticketCode}]");
                });
            }


            Mail::send('emails.support-request', [
                'name' => $request->name,
                'email' => $request->email,
                'issue_type' => $request->issue_type,
                'message_body' => $request->message,
                'submitted_at' => $submittedAt,
                'ticket_code' => $ticketCode, // ✅ tambahkan ini
            ], function ($message) use ($request, $supportEmail) {
                $fromEmail = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? $request->email : $supportEmail;

                $message->to($supportEmail)
                    ->subject('Customer Support Request from ' . $request->name)
                    ->replyTo($fromEmail)
                    ->from($fromEmail, $request->name);
            });


            LoggingService::logMfaEvent("Customer support message sent", [
                'user_id' => auth()->id() ?? null,
                'name' => $request->name,
                'email' => $request->email,
                'issue_type' => $request->issue_type, // TAMBAHKAN
                'ip' => $request->ip(),
                'message_snippet' => Str::limit($request->message, 100),
            ]);



            return back()->with('success', 'Your message has been sent. We will contact you soon.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send email. Please try again.']);
        }
    }
}
