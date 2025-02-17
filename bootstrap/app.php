<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\Verify2FAMiddleware;
use App\Http\Middleware\RoleMiddleware; // Import the RoleMiddleware
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register MFA Middleware
        $middleware->appendToGroup('mfachallenge', [Verify2FAMiddleware::class]);

        // Register Role Middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
