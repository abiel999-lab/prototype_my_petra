<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\Verify2FAMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\LdapAuthenticate;
use App\Http\Middleware\CheckBannedStatus;
use App\Http\Middleware\RestrictToMFA;
use App\Http\Middleware\IpRateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up' // ✅ No trailing comma
    )

    ->withMiddleware(function (Middleware $middleware) {
        // 🔐 Middleware Groups
        $middleware->appendToGroup('mfachallenge', [Verify2FAMiddleware::class]);
        $middleware->appendToGroup('restrict_to_mfa', [RestrictToMFA::class]);
        $middleware->appendToGroup('ip.limiter', [IpRateLimiter::class]);

        // 🧩 Middleware Aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'ldap.auth' => LdapAuthenticate::class,
            'check.banned' => CheckBannedStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling here if needed
    })
    ->create();
