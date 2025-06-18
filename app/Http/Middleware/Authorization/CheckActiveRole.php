<?php

namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActiveRole
{
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        $activeRole = session('active_role');

        // Fallback jika active_role kosong
        if (!$activeRole && Auth::check()) {
            $activeRole = Auth::user()->usertype;
            session(['active_role' => $activeRole]);
        }

        if (!in_array($activeRole, $roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
