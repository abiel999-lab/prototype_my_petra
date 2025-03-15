<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $userkey;
    protected $passkey;
    protected $apiUrl;

    public function __construct()
    {
        $this->userkey = env('ZENZIVA_USERKEY');
        $this->passkey = env('ZENZIVA_PASSKEY');
        $this->apiUrl = env('ZENZIVA_URL');
    }

    public function sendOTP($phone, $otpCode)
    {
        // Enhanced OTP message format
        $message = "*[My Petra] 🔐 Secure OTP Code*\n\n";
        $message .= "🔹 *Your OTP Code:* *$otpCode*\n";
        $message .= "❗ *Do not share this code with anyone.*\n";
        $message .= "⏳ *Expires in 5 minutes.*\n\n";
        $message .= "---------------------------\n";
        $message .= "📌 This code is for authentication purposes only.\n";
        $message .= "✅ If you requested this, proceed to enter the code.\n";
        $message .= "🚨 *If you did NOT request this, please ignore this message.*\n";
        $message .= "---------------------------\n\n";
        $message .= "🕒 *Sent on:* " . now()->format('d-m-Y H:i:s') . "\n";

        // Send OTP via WhatsApp API
        $response = Http::asForm()->post($this->apiUrl, [
            'userkey' => $this->userkey,
            'passkey' => $this->passkey,
            'to' => $phone,
            'message' => $message,
        ]);

        return $response->json();
    }
}
