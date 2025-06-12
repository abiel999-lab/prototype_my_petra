<?php

namespace App\Http\Middleware\Authentication;

use Closure;
use Illuminate\Http\Request;

class EnsureLdapOtpVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('ldap_otp_verified', false)) {
            return redirect()->route('ldap.otp.form')
                ->withErrors(['message' => 'You must verify OTP and identity before accessing this page.']);
        }

        return $next($request);
    }
}
