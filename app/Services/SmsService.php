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

    public function sendSms($phoneNumber, $message, $route = 'PREMIUM')
    {
        $phoneNumber = preg_replace('/^0/', '+62', $phoneNumber); // Convert 0815 → +62815

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
            ])->post('https://api.zuwinda.com/v2/messaging/sms/message', $payload);

            Log::info('Zuwinda SMS API Response:', $response->json());
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Zuwinda SMS API Error: ' . $e->getMessage());
            return ['error' => 'Failed to send SMS.'];
        }
    }

}
