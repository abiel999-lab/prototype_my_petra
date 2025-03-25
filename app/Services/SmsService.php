<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiUrl;
    protected $apiKey;
    protected $defaultRoute;

    public function __construct()
    {
        $this->apiUrl = config('services.zuwinda.api_url', 'https://api.zuwinda.com/v2/messaging/sms/message');
        $this->apiKey = config('services.zuwinda.api_key');
        $this->defaultRoute = config('services.zuwinda.default_route', 'PREMIUM'); // Default to OTP route
    }

    public function sendSms($phoneNumber, $otpCode, $route = 'OTP')
    {
        // Normalize to 08... (local format)
        if (str_starts_with($phoneNumber, '+62')) {
            $phoneNumber = '0' . substr($phoneNumber, 3);
        } else {
            $phoneNumber = preg_replace('/^\+62/', '0', $phoneNumber);
        }

        // Enhanced OTP message format
        $message = "[My Petra] Your OTP is: $otpCode\n";
        $message .= "Valid 5 mins. Do NOT share.\n";
        $message .= "If you did not request, ignore.\n";
        $message .= "mfa-mypetra.projects.petra.ac.id";
        $message .= "IMPORTANT!!!!\n";
        $message .= "This is an inactive number. Please kindly delete the number after using the OTP code.";

        Log::info("Sending SMS to: $phoneNumber | Route: $route | Message: $message");

        $payload = [
            'route' => $route,
            'to' => $phoneNumber,
            'content' => $message,
            'date' => '',
            'time' => ''
        ];

        try {
            $response = Http::withHeaders([
                'X-Access-Key' => env('ZUWINDA_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, $payload);

            Log::info('Zuwinda SMS API Response:', $response->json());
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Zuwinda SMS API Error: ' . $e->getMessage());
            return ['error' => 'Failed to send SMS.'];
        }
    }


}
