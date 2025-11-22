<?php

namespace App\Services;

use GuzzleHttp\Client;

class KeycloakAdminService
{
    protected string $baseUrl;
    protected string $realm;
    protected string $clientId;
    protected string $clientSecret;
    protected Client $http;

    public function __construct()
    {
        $this->baseUrl      = rtrim(env('KEYCLOAK_BASE_URL'), '/');
        $this->realm        = env('KEYCLOAK_REALM');
        $this->clientId     = env('KEYCLOAK_ADMIN_CLIENT_ID');
        $this->clientSecret = env('KEYCLOAK_ADMIN_CLIENT_SECRET');

        $this->http = new Client([
            'verify'  => false,
            'timeout' => 10,
        ]);
    }

    /**
     * Ambil admin access token untuk memanggil Admin API.
     */
    protected function getAdminToken(): string
    {
        $response = $this->http->post("{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token", [
            'form_params' => [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        return $json['access_token'];
    }

    /**
     * Ambil user ID Keycloak berdasarkan email.
     */
    protected function getUserIdByEmail(string $email): ?string
    {
        $token = $this->getAdminToken();

        $response = $this->http->get("{$this->baseUrl}/admin/realms/{$this->realm}/users", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
            'query' => [
                'email' => $email,
            ],
        ]);

        $users = json_decode($response->getBody()->getContents(), true);

        return $users[0]['id'] ?? null;
    }

    /**
     * Ambil satu group berdasarkan nama persis (exact match).
     */
    protected function getGroupByName(string $name): ?array
    {
        $token = $this->getAdminToken();

        // Bisa juga pakai ?search=, tapi kita filter manual supaya exact.
        $response = $this->http->get("{$this->baseUrl}/admin/realms/{$this->realm}/groups", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        $groups = json_decode($response->getBody()->getContents(), true);

        foreach ($groups as $group) {
            if (strcasecmp($group['name'], $name) === 0) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Ambil semua group yang sudah dimiliki user.
     */
    protected function getUserGroups(string $userId): array
    {
        $token = $this->getAdminToken();

        $response = $this->http->get("{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}/groups", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Pastikan user punya base group "role-{baseRole}" di Keycloak.
     *
     * Dipakai:
     * - login pertama (group masih kosong)
     * - boleh dipanggil setiap login, karena hanya ADD jika belum ada.
     *
     * baseRole: admin | staff | student | general
     */
    public function ensureBaseGroup(string $email, string $baseRole): void
    {
        if (!$email || !$baseRole) {
            return;
        }

        $userId = $this->getUserIdByEmail($email);
        if (!$userId) {
            return; // user belum ada di Keycloak? aneh, tapi jangan bikin error.
        }

        $groupName = match ($baseRole) {
            'admin'   => 'role-admin',
            'staff'   => 'role-staff',
            'student' => 'role-student',
            default   => 'role-guest',
        };

        $group = $this->getGroupByName($groupName);
        if (!$group) {
            // Group-nya belum dibuat di Keycloak, diamkan saja.
            return;
        }

        $currentGroups = $this->getUserGroups($userId);

        // Kalau user sudah punya group ini, tidak perlu apa-apa.
        foreach ($currentGroups as $g) {
            if (($g['id'] ?? null) === $group['id']) {
                return;
            }
        }

        // Tambahkan group ini ke user (tidak menghapus group lain).
        $token = $this->getAdminToken();

        $this->http->put(
            "{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}/groups/{$group['id']}",
            [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]
        );
    }

    /**
     * OPTIONAL:
     * Fungsi ini untuk full-sync group berdasarkan roles.
     * Tidak kita pakai sekarang, tapi boleh kamu gunakan nanti kalau mau.
     */
    public function syncUserGroupsHard(string $email, array $roles): void
    {
        // implementasi destruktif bisa kamu isi nanti kalau dibutuhkan.
    }
}
