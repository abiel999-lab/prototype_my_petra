<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCameFromMenuApp
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !session()->has('user_from_menu')) {
            return redirect('http://localhost:8000/login?blocked=1');
        }

        return $next($request);
    }
}

