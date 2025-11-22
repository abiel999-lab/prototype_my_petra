<?php

namespace App\Services;

use LdapRecord\Container;
use App\Models\Ldap\LocalUser;
use App\Models\Ldap\LocalGroup;


class LdapLocalProvisionService
{
    /**
     * Sinkronkan user dari Laravel ke LDAP local.
     *
     * @param  string  $email
     * @param  string  $firstName
     * @param  string  $lastName
     * @param  string  $systemRole  student|staff|admin|general
     * @return void
     */
    public function sync(string $email, string $firstName, string $lastName, string $systemRole): void
    {
        // Pastikan koneksi "local" dipakai
        Container::setDefaultConnection('local');

        // 1. Tentukan mapping role Laravel → OU & group LDAP
        $mapping = $this->mapSystemRoleToLdap($systemRole);

        $username = $this->buildUsernameFromEmail($email);
        $userDn   = "uid={$username},{$mapping['ou_dn']}";

        // 2. Cek user di LDAP
        /** @var LocalUser|null $ldapUser */
        $ldapUser = LocalUser::on('local')->find($userDn);

        if (! $ldapUser) {
            // 3. Buat user baru
            $ldapUser = new LocalUser();
            $ldapUser->setDn($userDn);

            $ldapUser->uid          = $username;
            $ldapUser->cn           = trim($firstName . ' ' . $lastName);
            $ldapUser->sn           = $lastName ?: $firstName;
            $ldapUser->mail         = $email;
            $ldapUser->ou           = $mapping['ou_attr'];     // staff / students / oa / guest
            $ldapUser->employeeType = $mapping['employee'];    // staff / student / admin / guest

            // Tidak set password (login via Keycloak/Google)
            $ldapUser->save();
        } else {
            // 4. Update info dasar
            $ldapUser->cn           = trim($firstName . ' ' . $lastName);
            $ldapUser->sn           = $lastName ?: $firstName;
            $ldapUser->mail         = $email;
            $ldapUser->ou           = $mapping['ou_attr'];
            $ldapUser->employeeType = $mapping['employee'];
            $ldapUser->save();
        }

        // 5. Pastikan member group role-*
        $this->attachUserToGroup($ldapUser->getDn(), $mapping['group_dn']);
    }

    protected function mapSystemRoleToLdap(string $systemRole): array
    {
        // default: general → guest
        $ouDn    = 'ou=guest,ou=people,dc=petra,dc=local';
        $groupDn = 'cn=role-guest,ou=groups,dc=petra,dc=local';
        $ouAttr  = 'guest';
        $employeeType = 'guest';

        switch ($systemRole) {
            case 'staff':
                $ouDn    = 'ou=staff,ou=people,dc=petra,dc=local';
                $groupDn = 'cn=role-staff,ou=groups,dc=petra,dc=local';
                $ouAttr  = 'staff';
                $employeeType = 'staff';
                break;

            case 'student':
                $ouDn    = 'ou=students,ou=people,dc=petra,dc=local';
                $groupDn = 'cn=role-student,ou=groups,dc=petra,dc=local';
                $ouAttr  = 'students';
                $employeeType = 'student';
                break;

            case 'admin':
                // admin Laravel = OA di LDAP
                $ouDn    = 'ou=oa,ou=people,dc=petra,dc=local';
                $groupDn = 'cn=role-oa,ou=groups,dc=petra,dc=local';
                $ouAttr  = 'oa';
                $employeeType = 'admin';
                break;

            case 'general':
            default:
                // sudah diset default di atas
                break;
        }

        return [
            'ou_dn'     => $ouDn,
            'group_dn'  => $groupDn,
            'ou_attr'   => $ouAttr,
            'employee'  => $employeeType,
        ];
    }

    protected function buildUsernameFromEmail(string $email): string
    {
        return strtolower(strtok($email, '@'));
    }

    protected function attachUserToGroup(string $userDn, string $groupDn): void
    {
        /** @var LocalGroup|null $group */
        $group = LocalGroup::on('local')->find($groupDn);

        if (! $group) {
            return;
        }

        $members = (array) $group->getAttribute('member', []);

        if (! in_array($userDn, $members, true)) {
            $members[] = $userDn;
            $group->setAttribute('member', $members);
            $group->save();
        }
    }
}
