<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;
    protected $accountId;

    public function __construct()
    {
        $this->apiUrl = config('services.zuwinda.whatsapp_url');
        $this->apiKey = config('services.zuwinda.api_key');
        $this->accountId = config('services.zuwinda.whatsapp_account_id');
    }

    public function sendOTP($phone, $otpCode)
    {

        // Construct rich OTP message
        $message = "[My Petra] Secure OTP Verification\n";
        $message .= "Your OTP Code: *$otpCode*\n"; // Buat bold agar user mudah salin
        $message .= "This code is valid for 5 minutes. Do NOT share this code with anyone.\n";
        $message .= "--------------------------------------\n";
        $message .= "For assistance, please contact our support team.\n";
        $message .= "https://mfa-mypetra.projects.petra.ac.id/customer-support\n";
        $message .= "--------------------------------------\n";
        $message .= "IMPORTANT!!!! This is an inactive number. Please kindly delete the number after using the OTP code.";

        // Send via Zuwinda WhatsApp API
        $response = Http::withHeaders([
            'X-Access-Key' => $this->apiKey,
        ])->post($this->apiUrl, [
                    'accountId' => $this->accountId,
                    'to' => $phone,
                    'messageType' => 'text',
                    'content' => $message,
                ]);

        return $response->json();
    }
    public function sendSupportTicket($phone, $ticketCode, $issueType, $name)
    {
        $message = "*[My Petra] Customer Support*\n";
        $message .= "Hello *$name*, thank you for contacting our support team.\n\n";
        $message .= "Your Ticket Code: *$ticketCode*\n";
        $message .= "Issue Type: $issueType\n";
        $message .= "Date: " . now('Asia/Jakarta')->format('d M Y H:i') . " WIB\n";
        $message .= "--------------------------------------\n";
        $message .= "We will respond as soon as possible.\n";
        $message .= "Track or update your request via:\n";
        $message .= "https://mfa-mypetra.projects.petra.ac.id/customer-support\n";
        $message .= "--------------------------------------\n";
        $message .= "_This is an automatic message. Please do not reply._";

        return Http::withHeaders([
            'X-Access-Key' => $this->apiKey,
        ])->post($this->apiUrl, [
                    'accountId' => $this->accountId,
                    'to' => $phone,
                    'messageType' => 'text',
                    'content' => $message,
                ])->json();
    }


}
