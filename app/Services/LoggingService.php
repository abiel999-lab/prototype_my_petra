<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    public static function logMfaEvent(string $message, array $context = []): void
    {
        $context['timestamp'] = now()->toDateTimeString();
        $context['ip'] = request()->ip();
        $context['user_agent'] = request()->userAgent();
        $context['user_id'] = auth()->id();


    }

    public static function logSecurityViolation(string $message, array $context = []): void
    {
        $context['timestamp'] = now()->toDateTimeString();
        $context['ip'] = request()->ip();
        $context['user_id'] = auth()->id();


    }
}
