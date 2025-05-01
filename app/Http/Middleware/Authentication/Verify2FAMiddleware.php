<?php

namespace App\Http\Middleware\Authentication;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TrustedDevice;
use Jenssegers\Agent\Agent;

class Verify2FAMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $agent = new Agent();
            $agent->setUserAgent($request->header('User-Agent'));

            $os = $agent->platform() ?? 'Unknown';

            $trustedDevice = TrustedDevice::where('user_id', $user->id)
                ->where('os', $os)
                ->where('trusted', true)
                ->first();

            if ($trustedDevice) {
                session(['two_factor_authenticated' => true]); // Skip MFA
            }

            if (!session()->get('two_factor_authenticated') && $user->mfa && $user->mfa->mfa_enabled) {
                return redirect()->route('mfa-challenge.index');
            }
        }

        return $next($request);
    }
}

