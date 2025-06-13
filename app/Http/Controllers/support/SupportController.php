<?php

namespace App\Http\Controllers\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        return view('auth.customer-support');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'issue_type' => 'required|string',
            'message' => 'required|string',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $needsAttachment = in_array($request->issue_type, ['login problem', 'MFA problem']);

        if ($needsAttachment) {
            $request->validate([
                'phone_number' => 'required|string',
                'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            ]);
        } else {
            $request->validate([
                'email' => 'required|email',
            ]);

            if (!\App\Models\User::where('email', $request->email)->exists()) {
                return back()->withErrors(['email' => 'Email is not registered in the system.']);
            }

            if (
                Ticketing::where('email', $request->email)
                    ->where('issue_type', $request->issue_type)
                    ->whereDate('created_at', Carbon::today())->exists()
            ) {
                return back()->withErrors(['message' => 'You have already submitted a similar issue today.']);
            }
        }

        $ip = $request->ip();
        $ticketCount = Ticketing::where('ip_address', $ip)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($ticketCount >= 2) {
            return back()->withErrors([
                'message' => 'You have reached the limit of 2 support requests today.',
            ]);
        }

        $supportEmail = env('MAIL_USERNAME', 'mfa.mypetra@petra.ac.id');
        $submittedAt = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB';

        try {
            $ticketCode = strtoupper(Str::random(6));
            $attachmentPath = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . $cleanName;
                $file->move(public_path('support_images'), $filename);
                $attachmentPath = 'support_images/' . $filename;
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

            if (!empty($request->phone_number)) {
                $wa = new WhatsAppService();
                $wa->sendSupportTicket($request->phone_number, $ticketCode, $request->issue_type, $request->name);
            }

            if (!empty($request->email) && $request->email !== '-') {
                Mail::send('emails.ticket-confirmation', [
                    'name' => $request->name,
                    'ticket_code' => $ticketCode,
                    'issue_type' => $request->issue_type,
                    'submitted_at' => $submittedAt,
                    'message_body' => $request->message,
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
                'ticket_code' => $ticketCode,
            ], function ($message) use ($request, $supportEmail, $attachmentPath) {
                $fromEmail = filter_var($request->email, FILTER_VALIDATE_EMAIL)
                    ? $request->email : $supportEmail;

                $message->to($supportEmail)
                    ->subject('Customer Support Request from ' . $request->name)
                    ->replyTo($fromEmail)
                    ->from($supportEmail, $request->name);

                if ($attachmentPath) {
                    $fullPath = public_path($attachmentPath);
                    if (file_exists($fullPath) && is_readable($fullPath)) {
                        $message->attach($fullPath, [
                            'as' => basename($fullPath),
                            'mime' => \Illuminate\Support\Facades\File::mimeType($fullPath),
                        ]);
                    }
                }

            });

            LoggingService::logMfaEvent("Customer support message sent", [
                'user_id' => auth()->id() ?? null,
                'name' => $request->name,
                'email' => $request->email,
                'issue_type' => $request->issue_type,
                'ip' => $request->ip(),
                'message_snippet' => Str::limit($request->message, 100),
            ]);

            return back()->with('success', 'Your message has been sent. We will contact you soon.');
        } catch (\Exception $e) {
            Log::error('SupportController Email Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send email. Please try again.']);
        }
    }
}
