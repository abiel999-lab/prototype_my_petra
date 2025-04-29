<?php

namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $activeRole = $user->temporary_role ?? $user->usertype;

        if (!in_array($activeRole, $roles)) {
            // Jika role tidak valid, paksa reset temporary role
            if ($user->usertype === 'admin' && $user->temporary_role !== null) {
                $user->temporary_role = null;
                $user->save();

                // Redirect kembali ke dashboard admin agar tidak stuck di 403
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Session reset to admin due to role conflict.');
            }

            abort(403, 'Forbidden');
        }

        return $next($request);
    }

}
