<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TrustedDevice;

class Verify2FAMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $ip = $request->ip();

            $trustedDevice = TrustedDevice::where('user_id', $user->id)
                ->where('ip_address', $ip)
                ->where('trusted', true)
                ->first();

            if ($trustedDevice) {
                session(['two_factor_authenticated' => true]); // Skip MFA
            }

            if (!session()->get('two_factor_authenticated') && $user->mfa_enabled) {
                return redirect()->route('mfa-challenge.index');
            }
        }

        return $next($request);
    }
}
