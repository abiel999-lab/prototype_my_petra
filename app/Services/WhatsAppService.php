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
        $message = "[My Petra] Secure OTP Verification\n\n";
        $message .= "Your OTP Code: $otpCode\n";
        $message .= "This code is valid for 5 minutes.\n";
        $message .= "Do NOT share this code with anyone.\n\n";
        $message .= "--------------------------------------\n";
        $message .= "This code is for secure authentication purposes only.\n";
        $message .= "If you requested this, enter the code to proceed.\n";
        $message .= "If you did NOT request this, please ignore this message.\n";
        $message .= "--------------------------------------\n\n";
        $message .= "Timestamp: " . now()->format('d-m-Y H:i:s') . "\n";
        $message .= "For assistance, please contact our support team.";
        $message .= "https://mfa-mypetra.projects.petra.ac.id/customer-support";
        $message .= "--------------------------------------\n\n";
        $message .= "IMPORTANT!!!!";
        $message .= "This is an inactive number. Please kindly delete the number after using the OTP code";

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
