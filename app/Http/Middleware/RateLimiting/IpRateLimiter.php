<?php

namespace App\Http\Middleware\RateLimiting;

use Closure;
use Illuminate\Support\Facades\Cache;

class IpRateLimiter
{
    public function handle($request, Closure $next)
    {
        $ip = $request->ip();
        $key = 'rate_limit_' . $ip;
        $limit = 20; // Maks 20 request per menit

        $count = Cache::get($key, 0);

        if ($count >= $limit) {
            abort(429, 'Too many requests.');
        }

        Cache::put($key, $count + 1, now()->addSeconds(60));
        return $next($request);
    }
}
