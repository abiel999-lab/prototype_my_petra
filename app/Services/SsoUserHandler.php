<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SsoUserHandler
{
    public function __construct(
        protected LdapLocalProvisionService $ldapProvision,
    ) {}

    /**
     * Handle user yang datang dari SSO (Keycloak / Google).
     *
     * @return \App\Models\User
     */
    public function handle(string $email, ?string $firstName, ?string $lastName): User
    {
        $email     = strtolower($email);
        $firstName = $firstName ?: Str::before($email, '@');
        $lastName  = $lastName ?? '';

        $systemRole = $this->resolveSystemRoleFromEmail($email);

        return DB::transaction(function () use ($email, $firstName, $lastName, $systemRole) {
            // 1. Simpan / update user Laravel
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => trim($firstName . ' ' . $lastName),
                    'role'     => $systemRole,             // kolom role/usertype di tabel users
                    'password' => bcrypt(Str::random(40)), // random karena login via SSO
                ]
            );

            if ($user->role !== $systemRole) {
                $user->role = $systemRole;
                $user->save();
            }

            // 2. Sinkron ke LDAP local
            $this->ldapProvision->sync(
                email: $email,
                firstName: $firstName,
                lastName: $lastName,
                systemRole: $systemRole,
            );

            return $user;
        });
    }

    /**
     * Mapping email → role Laravel (student|staff|general).
     * Admin kamu atur manual dari panel Laravel.
     */
    protected function resolveSystemRoleFromEmail(string $email): string
    {
        $domain = Str::after($email, '@');

        $staffDomains = [
            'peter.petra.ac.id',
            'petra.ac.id',
        ];

        $studentDomains = [
            'john.petra.ac.id',
            'alumni.petra.ac.id',
        ];

        if (in_array($domain, $staffDomains, true)) {
            return 'staff';
        }

        if (in_array($domain, $studentDomains, true)) {
            return 'student';
        }

        // default → general (guest)
        return 'general';
    }
}
