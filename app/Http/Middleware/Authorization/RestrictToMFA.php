<?php

namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictToMFA
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If user is restricted to MFA, redirect them
        if (session('restricted_to_mfa')) {
            return $this->redirectToMFASettings($user);
        }

        return $next($request);
    }

    private function redirectToMFASettings($user)
    {
        switch ($user->usertype) {
            case 'admin':
                return redirect()->route('profile.admin.mfa');
            case 'student':
                return redirect()->route('profile.student.mfa');
            case 'staff':
                return redirect()->route('profile.staff.mfa');
            default:
                return redirect()->route('profile.mfa');
        }
    }
}
