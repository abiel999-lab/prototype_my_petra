<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\Authentication\Verify2FAMiddleware;
use App\Http\Middleware\Authorization\RoleMiddleware;
use App\Http\Middleware\Authentication\LdapAuthenticate;
use App\Http\Middleware\Authorization\CheckBannedStatus;
use App\Http\Middleware\Authorization\RestrictToMFA;
use App\Http\Middleware\RateLimiting\IpRateLimiter;
use \App\Http\Middleware\Authorization\CheckActiveRole;

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
            'checkrole' => CheckActiveRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling here if needed
    })
    ->create();
