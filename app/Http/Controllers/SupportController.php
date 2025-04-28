<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Services\LoggingService;
use Carbon\Carbon;



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
            'email' => 'required|email',
            'issue_type' => 'required|string',
            'message' => 'required|string',
        ]);

        $supportEmail = env('MAIL_USERNAME', 'mfa.mypetra@petra.ac.id'); // Default email jika MAIL_USERNAME tidak diatur
        $submittedAt = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB';


        try {
            Mail::send('emails.support-request', [
                'name' => $request->name,
                'email' => $request->email,
                'issue_type' => $request->issue_type,
                'message_body' => $request->message,
                'submitted_at' => $submittedAt,
            ], function ($message) use ($request, $supportEmail) {
                $message->to($supportEmail)
                    ->subject('Customer Support Request from ' . $request->name)
                    ->replyTo($request->email)
                    ->from($request->email, $request->name);
            });



            LoggingService::logMfaEvent("Customer support message sent", [
                'user_id' => auth()->id() ?? null,
                'name' => $request->name,
                'email' => $request->email,
                'issue_type' => $request->issue_type, // TAMBAHKAN
                'ip' => $request->ip(),
                'message_snippet' => \Str::limit($request->message, 100),
            ]);



            return back()->with('success', 'Your message has been sent. We will contact you soon.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send email. Please try again.']);
        }
    }
}
