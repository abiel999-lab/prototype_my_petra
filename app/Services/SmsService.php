<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.zuwinda.api_url', 'https://api.zuwinda.com/v2/messaging/sms/message');
        $this->apiKey = config('services.zuwinda.api_key');
    }

    public function sendSms($phoneNumber, $otpCode)
    {
        // Normalisasi ke format lokal 08...
        if (str_starts_with($phoneNumber, '+62')) {
            $phoneNumber = '0' . substr($phoneNumber, 3);
        } else {
            $phoneNumber = preg_replace('/^\+62/', '0', $phoneNumber);
        }

        // Format pesan OTP
        $message = "[My Petra] Your OTP is: $otpCode\n";
        $message .= "Valid 5 mins. Do NOT share.\n";
        $message .= "If you did not request, ignore.\n";
        $message .= "mfa-mypetra.projects.petra.ac.id\n";
        $message .= "IMPORTANT!!!!\n";
        $message .= "This is an inactive number. Please kindly delete the number after using the OTP code.";

        // Siapkan payload JSON
        $payload = json_encode([
            'route' => 'OTP',
            'to' => $phoneNumber,
            'content' => $message,
            'date' => '',
            'time' => ''
        ]);

        // Kirim menggunakan cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'X-Access-Key: ' . $this->apiKey,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error('Zuwinda SMS API Error: ' . $error);
            return ['error' => 'Failed to send SMS.'];
        }

        Log::info('Zuwinda SMS API Response: ' . $response);
        return json_decode($response, true);
    }
}
