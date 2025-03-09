<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LdapAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $credentials = $request->only('email', 'password');

        // Try to authenticate via database first
        if (Auth::attempt($credentials)) {
            return $next($request);
        }

        // If database authentication fails, try LDAP
        $ldapUser = LdapUser::where('mail', $credentials['email'])->first();

        if ($ldapUser) {
            // Attempt to bind (authenticate) user with password
            if ($ldapUser->authenticate($credentials['password'])) {
                // Sync user to local database
                $user = User::updateOrCreate(
                    ['email' => $ldapUser->mail[0]], // Email is unique
                    [
                        'name' => $ldapUser->cn[0] ?? 'Unknown',
                        'password' => Hash::make($credentials['password']), // Sync password
                    ]
                );

                Auth::login($user); // Log in the user
                return $next($request);
            }
        }

        return redirect()->back()->withErrors(['email' => 'Invalid credentials']);
    }
}
