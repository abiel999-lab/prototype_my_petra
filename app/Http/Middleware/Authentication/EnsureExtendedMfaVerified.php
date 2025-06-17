<?php

namespace App\Http\Middleware\Authentication;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureExtendedMfaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (
            $user &&
            $user->mfa &&
            $user->mfa->extended_mfa_enabled
        ) {
            // Tangkap app_key dari path: /sso/{app_key}
            $segments = explode('/', $request->path());
            $appKey = $segments[1] ?? 'unknown';

            // Simpan appKey ke session agar bisa digunakan di controller
            session(['mfa_app_key' => $appKey]);

            // Jika belum diverifikasi untuk app ini
            if (!session("extended_mfa_verified.$appKey")) {
                return redirect()->guest(route('extended-mfa.challenge'));
            }
        }

        return $next($request);
    }
}
