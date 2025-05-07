<?php

namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request based on active role.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Ambil role aktif dari session, fallback ke usertype bawaan
        $activeRole = session('active_role') ?? $user->usertype;

        // Jika role aktif tidak diizinkan oleh route
        if (!in_array($activeRole, $roles)) {
            abort(403, 'Access denied: Role not permitted for this route.');
        }

        // Jika role aktif adalah identitas asli user, lanjutkan
        if ($activeRole === $user->usertype) {
            return $next($request);
        }

        // Jika bukan usertype, pastikan user memang punya akses tambahan ke role tersebut
        if (!$user->roles()->where('name', $activeRole)->exists()) {
            session()->forget('active_role'); // Reset session jika invalid
            return redirect()->route('home')->with('warning', 'Access denied. Role reset to default.');
        }

        return $next($request);
    }
}
