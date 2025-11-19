<?php

namespace App\Services;

use Laravel\Socialite\Contracts\User as SocialiteUser;
use LdapRecord\Container;
use LdapRecord\Connection;
use Illuminate\Support\Facades\Log;

class LdapGoogleSyncService
{
    /**
     * Dipanggil setelah Google OAuth sukses.
     * Ambil data dari Socialite lalu delegasikan ke syncFromLocal().
     */
    public function syncFromGoogle(SocialiteUser $googleUser): void
    {
        $email = $googleUser->getEmail();
        $name  = $googleUser->getName() ?? $googleUser->getNickname() ?? $email;

        $this->syncFromLocal($email, $name);
    }

    /**
     * Sinkron user ke LDAP dari data lokal (email + name).
     * Bisa dipanggil dari login lokal atau proses lain.
     *
     * RULE:
     * - john / alumni  -> student (Petra + local)
     * - peter / petra  -> staff   (Petra + local)
     * - lainnya        -> general (local ONLY)
     */
    public function syncFromLocal(string $email, string $name): void
    {
        $uid       = explode('@', $email)[0];
        $userType  = $this->detectUserType($email); // student / staff / general

        // 1) Kalau student → sync ke Petra + local
        if ($userType === 'student') {
            // Petra LDAP (student connection)
            $this->safeSyncPetraStudent($uid, $name, $email);

            // Local LDAP (student)
            $this->safeSyncLocal($uid, $name, $email, 'student');
            return;
        }

        // 2) Kalau staff → sync ke Petra + local
        if ($userType === 'staff') {
            // Petra LDAP (staff / default connection)
            $this->safeSyncPetraStaff($uid, $name, $email);

            // Local LDAP (staff)
            $this->safeSyncLocal($uid, $name, $email, 'staff');
            return;
        }

        // 3) Selain itu (gmail, dll) -> HANYA local, type = general
        $this->safeSyncLocal($uid, $name, $email, 'general');
    }

    /**
     * Deteksi tipe user dari email.
     * - john / alumni -> student
     * - peter / petra -> staff
     * - lainnya       -> general
     */
    protected function detectUserType(string $email): string
    {
        $email = strtolower($email);

        if (str_ends_with($email, '@john.petra.ac.id')) {
            return 'student';
        }

        if (str_ends_with($email, '@alumni.petra.ac.id')) {
            return 'student';
        }

        if (str_ends_with($email, '@peter.petra.ac.id')) {
            return 'staff';
        }

        if (str_ends_with($email, '@petra.ac.id')) {
            return 'staff';
        }

        return 'general';
    }

    /* ============================================================
     *  PETRA LDAP SYNC (default & student)
     *  ============================================================
     */

    /**
     * Sync student ke LDAP Petra (connection: student).
     */
    protected function safeSyncPetraStudent(string $uid, string $name, string $email): void
    {
        try {
            /** @var Connection $connection */
            $connection = Container::getConnection(env('LDAP_STUDENT_CONNECTION', 'student'));

            $baseDnRoot = trim(env('LDAP_STUDENT_BASE_DN', 'dc=petra,dc=ac,dc=id'), '"');
            $baseDn     = 'ou=students,' . $baseDnRoot;

            $this->upsertLdapUserWithAdminRebind(
                $connection,
                $baseDn,
                $uid,
                $name,
                $email,
                'student'
            );
        } catch (\Throwable $e) {
            Log::warning('Petra LDAP student sync failed', [
                'error' => $e->getMessage(),
                'uid'   => $uid,
                'email' => $email,
            ]);
        }
    }

    /**
     * Sync staff ke LDAP Petra (connection: default).
     */
    protected function safeSyncPetraStaff(string $uid, string $name, string $email): void
    {
        try {
            /** @var Connection $connection */
            $connection = Container::getConnection(env('LDAP_CONNECTION', 'default'));

            $baseDnRoot = trim(env('LDAP_BASE_DN', 'dc=petra,dc=ac,dc=id'), '"');
            $baseDn     = 'ou=staff,' . $baseDnRoot;

            $this->upsertLdapUserWithAdminRebind(
                $connection,
                $baseDn,
                $uid,
                $name,
                $email,
                'staff'
            );
        } catch (\Throwable $e) {
            Log::warning('Petra LDAP staff sync failed', [
                'error' => $e->getMessage(),
                'uid'   => $uid,
                'email' => $email,
            ]);
        }
    }

    /**
     * ADD / MODIFY user di LDAP PETRA.
     * Versi ini pakai "admin rebind" (model lama kamu) karena akun jaringan
     * kadang read-only.
     */
    protected function upsertLdapUserWithAdminRebind(
        Connection $connection,
        string $baseDn,
        string $uid,
        string $name,
        string $email,
        string $type // student / staff
    ): void {
        $connection->connect();
        $ldap = $connection->getLdapConnection();

        $sn = $this->extractSurname($name);

        $attributes = [
            'objectClass' => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
            'uid'         => $uid,
            'cn'          => $name,
            'sn'          => $sn,
            'mail'        => $email,
        ];

        // 1️⃣ Cek apakah sudah ada
        $filter  = '(uid=' . $this->ldapEscapeFilter($uid) . ')';
        $result  = $ldap->search($baseDn, $filter, ['dn']);
        $entries = $ldap->getEntries($result);

        // 2️⃣ Simpan credential asli (bind sebelumnya)
        $originalDn = env('LDAP_USERNAME');
        $originalPw = env('LDAP_PASSWORD');

        // 3️⃣ Rebind sementara ke admin (pakai env admin Petra)
        $adminDn = env('LDAP_STUDENT_USERNAME', 'cn=admin,dc=petra,dc=ac,dc=id');
        $adminPw = env('LDAP_STUDENT_PASSWORD', 'LD4P53rv3r@UKP');

        try {
            $ldap->bind($adminDn, $adminPw);
        } catch (\Exception $e) {
            Log::error('Petra LDAP admin bind failed before write', [
                'error' => $e->getMessage(),
                'uid'   => $uid,
                'email' => $email,
                'type'  => $type,
            ]);
            // lanjut saja, add/modify kemungkinan gagal.
        }

        try {
            if (!isset($entries['count']) || $entries['count'] == 0) {
                // === Belum ada → ADD ===
                $dn = 'uid=' . $uid . ',' . $baseDn;
                $ldap->add($dn, $attributes);

                Log::info('Petra LDAP user created (using escalated admin bind)', [
                    'dn'    => $dn,
                    'uid'   => $uid,
                    'email' => $email,
                    'type'  => $type,
                ]);
            } else {
                // === Sudah ada → MODIFY ===
                $dn = $entries[0]['dn'];
                $ldap->modify($dn, [
                    'cn'   => $name,
                    'sn'   => $sn,
                    'mail' => $email,
                ]);

                Log::info('Petra LDAP user updated (using escalated admin bind)', [
                    'dn'    => $dn,
                    'uid'   => $uid,
                    'email' => $email,
                    'type'  => $type,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Petra LDAP write failed (even after admin bind)', [
                'error' => $e->getMessage(),
                'uid'   => $uid,
                'email' => $email,
                'type'  => $type,
            ]);
        }

        // 4️⃣ Bind balik ke akun jaringan lagi
        if ($originalDn && $originalPw) {
            try {
                $ldap->bind($originalDn, $originalPw);
            } catch (\Exception $e) {
                Log::warning('Petra LDAP: Failed to rebind to original user after admin write', [
                    'error' => $e->getMessage(),
                    'user'  => $originalDn,
                ]);
            }
        }
    }

    /* ============================================================
     *  LOCAL LDAP SYNC (dc=petra,dc=local)
     *  ============================================================
     */

    /**
     * Sync ke LDAP local (connection: local).
     * $type: student / staff / general
     *
     * - student -> ou=students,ou=people,dc=petra,dc=local
     * - staff   -> ou=staff,ou=people,dc=petra,dc=local
     * - general -> ou=people,dc=petra,dc=local
     */
    protected function safeSyncLocal(string $uid, string $name, string $email, string $type): void
    {
        try {
            /** @var Connection $connection */
            $connection = Container::getConnection(env('LDAP_LOCAL_CONNECTION', 'local'));

            $baseDnRoot = trim(env('LDAP_LOCAL_BASE_DN', 'dc=petra,dc=local'), '"');

            if ($type === 'student') {
                $baseDn = 'ou=students,ou=people,' . $baseDnRoot;
            } elseif ($type === 'staff') {
                $baseDn = 'ou=staff,ou=people,' . $baseDnRoot;
            } else {
                // general → langsung di bawah ou=people
                $baseDn = 'ou=people,' . $baseDnRoot;
            }

            $this->upsertLocalLdapUser($connection, $baseDn, $uid, $name, $email, $type);
        } catch (\Throwable $e) {
            Log::error('Local LDAP sync failed', [
                'error' => $e->getMessage(),
                'uid'   => $uid,
                'email' => $email,
                'type'  => $type,
            ]);
        }
    }

    /**
     * ADD / MODIFY user di LDAP LOCAL (tanpa rebind aneh-aneh).
     * Diasumsikan connection "local" sudah pakai admin bind di config ldap.php.
     */
    protected function upsertLocalLdapUser(
        Connection $connection,
        string $baseDn,
        string $uid,
        string $name,
        string $email,
        string $type
    ): void {
        $connection->connect();
        $ldap = $connection->getLdapConnection();

        $sn = $this->extractSurname($name);

        $attributes = [
            'objectClass'  => ['inetOrgPerson', 'organizationalPerson', 'person', 'top'],
            'uid'          => $uid,
            'cn'           => $name,
            'sn'           => $sn,
            'mail'         => $email,
            'employeeType' => $type, // student / staff / general
        ];

        // Cek apakah sudah ada di local
        $filter  = '(uid=' . $this->ldapEscapeFilter($uid) . ')';
        $result  = $ldap->search($baseDn, $filter, ['dn']);
        $entries = $ldap->getEntries($result);

        try {
            if (!isset($entries['count']) || $entries['count'] == 0) {
                // === Belum ada → ADD ===
                $dn = 'uid=' . $uid . ',' . $baseDn;
                $ldap->add($dn, $attributes);

                Log::info('Local LDAP user created', [
                    'dn'    => $dn,
                    'uid'   => $uid,
                    'email' => $email,
                    'type'  => $type,
                ]);
            } else {
                // === Sudah ada → MODIFY ===
                $dn = $entries[0]['dn'];
                $ldap->modify($dn, [
                    'cn'           => $name,
                    'sn'           => $sn,
                    'mail'         => $email,
                    'employeeType' => $type,
                ]);

                Log::info('Local LDAP user updated', [
                    'dn'    => $dn,
                    'uid'   => $uid,
                    'email' => $email,
                    'type'  => $type,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Local LDAP write failed', [
                'error' => $e->getMessage(),
                'uid'   => $uid,
                'email' => $email,
                'type'  => $type,
            ]);
        }
    }

    /* ============================================================
     *  UTIL
     *  ============================================================
     */

    /**
     * Ambil nama belakang.
     */
    protected function extractSurname(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        if (empty($parts)) return $name;
        if (count($parts) === 1) return $parts[0];
        return end($parts);
    }

    /**
     * Escape string agar aman di filter LDAP.
     */
    protected function ldapEscapeFilter(string $value): string
    {
        return str_replace(
            ['\\',   '*',   '(',   ')',   "\x00"],
            ['\\5c', '\\2a','\\28','\\29','\\00'],
            $value
        );
    }
}
