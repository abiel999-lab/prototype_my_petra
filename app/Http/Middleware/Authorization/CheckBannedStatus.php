<?php

namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckBannedStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // ✅ Ensure the user is logged in
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'You must be logged in to continue.']);
        }

        // ✅ Auto-reset login ban if expired
        if ($user->login_ban_until && Carbon::parse($user->login_ban_until)->lt(now())) {
            $user->update(['login_ban_until' => null, 'failed_login_attempts' => 0]);
        }

        // ✅ Check Login Ban (redirect to login if still banned)
        if ($user->login_ban_until && Carbon::parse($user->login_ban_until)->gt(now())) {
            $banEnd = Carbon::parse($user->login_ban_until)->setTimezone('Asia/Jakarta')->format('H:i:s');
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => "Your account is temporarily locked until {$banEnd} Jakarta Time."
            ]);
        }

        // ✅ Auto-reset OTP ban if expired
        if ($user->mfa && $user->mfa->otp_ban_until && Carbon::parse($user->mfa->otp_ban_until)->lt(now())) {
            $user->mfa->update([
                'otp_ban_until' => null,
            ]);
            $user->failed_otp_attempts = 0;
            $user->save();
        }


        // ✅ Check OTP Ban (redirect to OTP page if still banned)
        if ($user->mfa && $user->mfa->otp_ban_until && Carbon::parse($user->mfa->otp_ban_until)->gt(now())) {
            $banEnd = Carbon::parse($user->mfa->otp_ban_until)->setTimezone('Asia/Jakarta')->format('H:i:s');
            return redirect()->route('mfa-challenge.index')->withErrors([
                'code' => "Too many incorrect OTP attempts. You are banned until {$banEnd} Jakarta Time."
            ]);
        }

        // ✅ Auto logout and force login after 20 failed OTP attempts
        if ($user->failed_otp_attempts >= 20) {
            $user->mfa->update([
                'otp_ban_until' => now()->addMinutes(30),
            ]);
            $user->failed_otp_attempts = 0;
            $user->save();

            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Too many OTP failures. You have been temporarily locked out for 30 minutes.'
            ]);
        }

        return $next($request);
    }
}
