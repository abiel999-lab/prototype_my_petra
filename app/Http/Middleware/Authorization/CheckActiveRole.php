<?php

// app/Http/Middleware/CheckActiveRole.php
namespace App\Http\Middleware\Authorization;

use Closure;
use Illuminate\Http\Request;

class CheckActiveRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $activeRole = session('active_role');

        if (!in_array($activeRole, $roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}

