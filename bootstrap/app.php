<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\Verify2FAMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\LdapAuthenticate; // Import LDAP Middleware
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckBannedStatus; // ✅ Import the banned status middleware
use App\Http\Middleware\RestrictToMFA;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up' // ✅ Removed the trailing comma here
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register MFA Middleware
        $middleware->appendToGroup('mfachallenge', [Verify2FAMiddleware::class]);

        // Register Role Middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'ldap.auth' => LdapAuthenticate::class, // Register LDAP Authentication Middleware
            'check.banned' => CheckBannedStatus::class, // ✅ Register banned user middleware

        ]);
        $middleware->appendToGroup('restrict_to_mfa', [RestrictToMFA::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
