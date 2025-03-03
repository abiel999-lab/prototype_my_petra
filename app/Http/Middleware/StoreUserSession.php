<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            session([
                'user_id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_time' => now()
            ]);
        }
        return $next($request);
    }
}

