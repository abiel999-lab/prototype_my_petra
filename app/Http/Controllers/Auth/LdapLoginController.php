<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Ldap\LocalUser as LocalLdapUser;
use App\Ldap\StaffUser as StaffLdapUser;
use App\Ldap\StudentUser as StudentLdapUser;
use App\Models\User;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LdapRecord\Container;

class LdapLoginController extends Controller
{
    public function login(Request $request)
    {
        // 1) Bentuk email utuh
        $email = $request->has('emailLocalPart')
            ? $request->emailLocalPart . $request->emailDomain
            : $request->email;

        $credentials = [
            'email'    => $email,
            'password' => $request->password,
        ];

        // 2) Coba login ke DATABASE dulu (user lokal)
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            session(['active_role' => $user->usertype]);

            return match ($user->usertype) {
                'admin'   => redirect()->route('admin.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                'staff'   => redirect()->route('staff.dashboard'),
                default   => redirect()->route('dashboard'),
            };
        }

        // 3) Kalau gagal DB, baru coba LDAP (Petra staff → student → lokal)
        try {
            $ldapUser       = null;
            $connectionName = null;

            // 3a) Petra STAFF
            try {
                $ldapUser = StaffLdapUser::where('mail', '=', $credentials['email'])->first();

                if ($ldapUser) {
                    $connectionName = 'default';
                }
            } catch (\Throwable $e) {
                LoggingService::logSecurityViolation("LDAP Petra staff search error: " . $e->getMessage());
            }

            // 3b) Petra STUDENT
            if (! $ldapUser) {
                try {
                    $ldapUser = StudentLdapUser::where('mail', '=', $credentials['email'])->first();

                    if ($ldapUser) {
                        $connectionName = 'student';
                    }
                } catch (\Throwable $e) {
                    LoggingService::logSecurityViolation("LDAP Petra student search error: " . $e->getMessage());
                }
            }

            // 3c) LDAP LOCAL (docker)
            if (! $ldapUser) {
                try {
                    $ldapUser = LocalLdapUser::where('mail', '=', $credentials['email'])->first();

                    if ($ldapUser) {
                        $connectionName = 'local';
                    }
                } catch (\Throwable $e) {
                    LoggingService::logSecurityViolation("LDAP local search error: " . $e->getMessage());
                }
            }

            // 4) Kalau ketemu di salah satu LDAP, lakukan BIND AUTH
            if ($ldapUser && $connectionName) {
                try {
                    $connection = Container::getConnection($connectionName);

                    $ok = $connection->auth()->attempt(
                        $ldapUser->getDn(),
                        $credentials['password']
                    );
                } catch (\Throwable $e) {
                    LoggingService::logSecurityViolation("LDAP bind error on [$connectionName]: " . $e->getMessage());
                    $ok = false;
                }

                if ($ok) {
                    // 5) Sinkron ke tabel users lokal
                    $ldapEmail = $ldapUser->getFirstAttribute('mail') ?? $credentials['email'];
                    $display   = $ldapUser->getFirstAttribute('cn') ?? 'Unknown';

                    $user       = User::where('email', $ldapEmail)->first();
                    $firstLogin = ! $user;

                    if ($firstLogin) {
                        $lowerEmail = strtolower($ldapEmail);
                        $usertype   = 'general';

                        if (str_ends_with($lowerEmail, '@john.petra.ac.id')) {
                            $usertype = 'student';
                        } elseif (str_ends_with($lowerEmail, '@alumni.petra.ac.id')) {
                            $usertype = 'student';
                        } elseif (str_ends_with($lowerEmail, '@peter.petra.ac.id')) {
                            $usertype = 'staff';
                        } elseif (str_ends_with($lowerEmail, '@petra.ac.id')) {
                            $usertype = 'staff';
                        } elseif ($connectionName === 'local') {
                            $usertype = 'general';
                        }

                        $user = User::create([
                            'email'    => $ldapEmail,
                            'name'     => $display,
                            'password' => Hash::make(uniqid('ldap_', true)),
                            'usertype' => $usertype,
                        ]);
                    }

                    Auth::login($user);
                    session(['active_role' => $user->usertype]);

                    LoggingService::logMfaEvent("LDAP login success via [$connectionName] for {$user->email}", [
                        'user_id' => $user->id,
                        'ip'      => request()->ip(),
                    ]);

                    return match ($user->usertype) {
                        'admin'   => redirect()->route('admin.dashboard'),
                        'student' => redirect()->route('student.dashboard'),
                        'staff'   => redirect()->route('staff.dashboard'),
                        default   => redirect()->route('dashboard'),
                    };
                }
            }
        } catch (\Throwable $e) {
            LoggingService::logSecurityViolation("LDAP login global error for {$credentials['email']}: " . $e->getMessage());
        }

        // 6) Kalau semua gagal
        return back()->withErrors(['email' => 'Invalid credentials']);
    }
}
