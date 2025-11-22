<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\KeycloakJwtService;
use App\Services\KeycloakAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

class KeycloakController extends Controller
{
    protected Keycloak $provider;
    protected KeycloakJwtService $jwtService;
    protected KeycloakAdminService $kcAdmin;

    public function __construct(KeycloakJwtService $jwtService, KeycloakAdminService $kcAdmin)
    {
        $config = config('services.keycloak');

        $this->provider = new Keycloak([
            'authServerUrl' => $config['base_url'],
            'realm'         => $config['realm'],
            'clientId'      => $config['client_id'],
            'clientSecret'  => $config['client_secret'],
            'redirectUri'   => $config['redirect_uri'],
        ]);

        $this->jwtService = $jwtService;
        $this->kcAdmin    = $kcAdmin;
    }

    public function redirectToProvider(Request $request)
    {
        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => 'openid email profile',
        ]);

        $request->session()->regenerate();
        $request->session()->put('keycloak_oauth_state', $this->provider->getState());

        return redirect()->away($authUrl);
    }

    public function handleCallback(Request $request)
    {
        $expectedState = $request->session()->pull('keycloak_oauth_state');

        if ($request->get('state') !== $expectedState) {
            abort(403, 'Invalid OAuth state');
        }

        if ($request->has('error')) {
            return redirect()->route('login')->withErrors([
                'keycloak' => $request->get('error_description', 'Login via SSO gagal'),
            ]);
        }

        // Tukar "code" jadi access token
        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);

        $claims = $this->jwtService->decodeAndVerify($token->getToken());

        $email    = strtolower($claims->email ?? '');
        $username = $claims->preferred_username ?? $claims->sub ?? null;

        $realmRoles = $claims->realm_access->roles ?? [];
        $groups     = $claims->groups ?? [];

        // Log debug kalau mau lihat isi token:
        Log::info('Keycloak claims', [
            'email'      => $email,
            'realmRoles' => $realmRoles,
            'groups'     => $groups,
        ]);

        // 1) Buat / ambil user lokal
        $user = $this->findOrCreateLocalUser($email, $username, $claims);

        // 2) Ambil roles dari Keycloak
        $rolesFromKeycloak = $this->extractRolesFromKeycloak($realmRoles, $groups);

        // 3) Tentukan baseRole + handle first login
        if (empty($rolesFromKeycloak)) {
            // Belum ada role sama sekali di Keycloak
            $baseRole          = $this->fallbackEmailRole($email);  // student/staff/general
            $rolesFromKeycloak = [$baseRole];

            $this->safeEnsureBaseGroup($email, $baseRole);
        } else {
            // Ada role di Keycloak, pilih yang tertinggi
            $baseRole = $this->pickBaseRole($rolesFromKeycloak);

            $this->safeEnsureBaseGroup($email, $baseRole);
        }

        // 4) Sync DB
        $this->updateUsertype($user, $baseRole);
        $this->updateRoleUserTable($user, $rolesFromKeycloak);

        // 5) Login
        Auth::login($user, true);

        // 6) Redirect sesuai baseRole
        return redirect()->intended($this->redirectByBaseRole($baseRole));
    }

    // =====================================================================
    // USER CREATION
    // =====================================================================

    protected function findOrCreateLocalUser(string $email, ?string $username, object $claims): User
    {
        if (!$email && !$username) {
            abort(400, 'Token tidak memiliki email atau username.');
        }

        $user  = User::where('email', $email)->first();
        $isNew = !$user;

        if (!$user) {
            $user = new User();
            $user->name     = $username ?? $email;
            $user->email    = $email;
            $user->password = bcrypt(str()->random(32));
        }

        if (Schema::hasColumn('users', 'sso_provider')) {
            $user->sso_provider = 'keycloak';
        }

        if (Schema::hasColumn('users', 'sso_subject')) {
            $user->sso_subject = $claims->sub ?? null;
        }

        if ($isNew && Schema::hasColumn('users', 'usertype') && empty($user->usertype)) {
            $user->usertype = $this->fallbackEmailRole($email);
        }

        $user->save();

        return $user;
    }

    // =====================================================================
    // ROLE MAPPING
    // =====================================================================

    /**
     * Ambil roles dari Keycloak:
     * - dari groups: role-admin / role-oa / role-staff / role-student / role-guest
     * - dari realm roles: admin / oa / staff / student / guest
     *
     * oa = admin, guest = general.
     */
    protected function extractRolesFromKeycloak(array $realmRoles, array $groups): array
    {
        $roles = [];

        // --- 1) Dari GROUPS ---
        $cleanGroups = [];
        foreach ($groups as $g) {
            if (!is_string($g)) {
                continue;
            }
            // "/role-admin" atau "/something/role-admin" → "role-admin"
            if (str_contains($g, '/')) {
                $g = substr($g, strrpos($g, '/') + 1);
            }
            $cleanGroups[] = strtolower($g);
        }

        // admin / oa -> admin
        if (in_array('role-admin', $cleanGroups, true) ||
            in_array('role-oa', $cleanGroups, true)) {
            $roles[] = 'admin';
        }

        // staff
        if (in_array('role-staff', $cleanGroups, true)) {
            $roles[] = 'staff';
        }

        // student
        if (in_array('role-student', $cleanGroups, true)) {
            $roles[] = 'student';
        }

        // guest -> general
        if (in_array('role-guest', $cleanGroups, true)) {
            $roles[] = 'general';
        }

        // --- 2) Dari REALM ROLES ---
        $lowerRealm = array_map('strtolower', $realmRoles);

        if (in_array('admin', $lowerRealm, true) ||
            in_array('oa', $lowerRealm, true)) {
            $roles[] = 'admin';
        }
        if (in_array('staff', $lowerRealm, true)) {
            $roles[] = 'staff';
        }
        if (in_array('student', $lowerRealm, true)) {
            $roles[] = 'student';
        }
        if (in_array('guest', $lowerRealm, true)) {
            $roles[] = 'general';
        }

        $roles = array_values(array_unique($roles));

        // filter hanya role yang kita kenal
        return array_values(array_filter($roles, fn($r) =>
            in_array($r, ['admin', 'staff', 'student', 'general'], true)
        ));
    }

    /**
     * Pilih role tertinggi: admin > staff > student > general
     */
    protected function pickBaseRole(array $roles): string
    {
        $priority = [
            'general' => 0,
            'student' => 1,
            'staff'   => 2,
            'admin'   => 3,
        ];

        $best = 'general';
        foreach ($roles as $r) {
            if ($priority[$r] > $priority[$best]) {
                $best = $r;
            }
        }

        return $best;
    }

    /**
     * Fallback role dari email:
     * john/alumni → student
     * peter/petra → staff
     * lainnya     → general
     */
    protected function fallbackEmailRole(string $email): string
    {
        $email = strtolower($email);

        if (preg_match('/@(john\.petra\.ac\.id|alumni\.petra\.ac\.id)$/', $email)) {
            return 'student';
        }

        if (preg_match('/@(peter\.petra\.ac\.id|petra\.ac\.id)$/', $email)) {
            return 'staff';
        }

        return 'general';
    }

    protected function safeEnsureBaseGroup(string $email, string $baseRole): void
    {
        try {
            $this->kcAdmin->ensureBaseGroup($email, $baseRole);
        } catch (\Throwable $e) {
            Log::warning('Keycloak ensureBaseGroup gagal: '.$e->getMessage());
        }
    }

    // =====================================================================
    // SYNC DB
    // =====================================================================

    protected function updateUsertype(User $user, string $baseRole): void
    {
        if (!Schema::hasColumn('users', 'usertype')) {
            return;
        }

        if ($user->usertype !== $baseRole) {
            $user->usertype = $baseRole;
            $user->save();
        }
    }

    protected function updateRoleUserTable(User $user, array $roles): void
    {
        if (empty($roles)) {
            $user->roles()->sync([]);
            return;
        }

        $roleIds = Role::whereIn('name', $roles)->pluck('id')->all();

        $user->roles()->sync($roleIds);
    }

    // =====================================================================
    // REDIRECT
    // =====================================================================

    protected function redirectByBaseRole(string $baseRole): string
    {
        return match ($baseRole) {
            'admin'   => route('admin.dashboard'),
            'staff'   => route('staff.dashboard'),
            'student' => route('student.dashboard'),
            default   => route('dashboard'),
        };
    }
}
