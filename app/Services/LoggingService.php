<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    public static function logMfaEvent(string $message, array $context = [])
    {
        $context['timestamp'] = now()->toDateTimeString();
        $context['ip'] = request()->ip();
        $context['user_agent'] = request()->userAgent();
        $context['user_id'] = auth()->id();

        Log::channel('mfa')->info($message, $context);
    }

    public static function logSecurityViolation(string $message, array $context = [])
    {
        $context['timestamp'] = now()->toDateTimeString();
        $context['ip'] = request()->ip();
        $context['user_id'] = auth()->id();

        Log::channel('mfa')->warning($message, $context);
    }
}
