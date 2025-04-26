<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TrustedDevice;
use Jenssegers\Agent\Agent;
use App\Services\LoggingService;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $mfaEnabled = $request->input('mfa_enabled');
        $mfaMethod = $request->input('mfa_method');
        $userType = $request->input('usertype');

        $usersQuery = User::query();

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($mfaEnabled === "on" || $mfaEnabled === "yes" || $mfaEnabled === "1") {
            $usersQuery->where('mfa_enabled', 1);
        } elseif ($mfaEnabled === "off" || $mfaEnabled === "no" || $mfaEnabled === "0") {
            $usersQuery->where('mfa_enabled', 0);
        }

        if ($mfaMethod) {
            $usersQuery->where('mfa_method', $mfaMethod);
        }

        if ($userType) {
            $usersQuery->where('usertype', $userType);
        }

        $users = $usersQuery->with('devices')->paginate(5);

        foreach ($users as $user) {

            // Ambil sesi dari DB
            $rawSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderBy('last_activity', 'desc')
                ->get();

            $activeSessions = [];

            foreach ($rawSessions as $session) {
                // Ambil user_agent dari payload session
                $userAgent = $session->user_agent ?? 'Unknown';


                $agent = new Agent();
                $agent->setUserAgent($userAgent);

                $device = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');

                $lastActivity = Carbon::createFromTimestamp($session->last_activity);
                $expiresAt = $lastActivity->copy()->addMinutes((int) config('session.lifetime'));

                $activeSessions[] = [
                    'id' => $session->id, // ⬅️ Tambahkan ini!
                    'ip' => $session->ip_address ?? 'Unknown',
                    'device' => $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown'),
                    'os' => $agent->platform() ?: 'Unknown',
                    'browser' => $agent->browser() ?: 'Unknown',
                    'login_at' => $lastActivity->format('d M Y H:i'),
                    'expires_at' => $expiresAt->format('d M Y H:i'),
                ];

            }

            $user->active_sessions = $activeSessions;
        }

        return view('profile.admin.manage-user', compact('users', 'search'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'usertype' => 'required|string|in:admin,staff,student,general'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'usertype' => $request->usertype, // Ensure usertype is stored correctly
        ]);
        LoggingService::logMfaEvent("Admin created a new user", [
            'admin_id' => auth()->id(),
            'created_email' => $request->email,
            'usertype' => $request->usertype,
        ]);
        // 🔐 Sinkronisasi ke Active Directory
        try {
            $uid = explode('@', $request->email)[0];

            $ldap = new LdapUser;
            $ldap->cn = $request->name;
            $ldap->sAMAccountName = $uid; // ID login di AD
            $ldap->userPrincipalName = $request->email;
            $ldap->mail = $request->email;

            // Format password untuk AD (UTF-16LE dan dalam tanda kutip)
            $quotedPwd = iconv('UTF-8', 'UTF-16LE', '"' . $request->password . '"');
            $ldap->unicodePwd = $quotedPwd;

            // DN penempatan
            $ldap->setDn("cn={$request->name},ou=staff,dc=petra,dc=ac,dc=id");

            $ldap->save();

            LoggingService::logMfaEvent("Synced new user to LDAP (AD): {$request->email}", []);
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("LDAP sync failed (AD) for user {$request->email}: " . $e->getMessage(), []);
        }


        return redirect()
            ->route('profile.admin.manageuser')
            ->with('success', 'User created successfully.');

    }




    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'usertype' => 'required|string|in:general,admin,staff,student',
            'mfa_enabled' => 'nullable|boolean',
            'mfa_method' => 'required|string|in:email,google_auth,whatsapp,sms',
        ]);

        // Convert checkbox value to boolean (1 or 0)
        $mfa_enabled = $request->input('mfa_enabled', 0); // Default to 0 if not present
        $newMethod = $request->input('mfa_method');
        $oldMethod = $user->mfa_method;

        // ❌ Block switching to Google Auth if user has no secret
        if ($newMethod === 'google_auth' && empty($user->google2fa_secret)) {
            return redirect()->back()->with('error', 'User has not activated Mobile Authenticator.');
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'usertype' => $request->usertype,
            'mfa_enabled' => $mfa_enabled,
            'mfa_method' => $request->mfa_method,
        ]);
        LoggingService::logMfaEvent("Admin updated user [ID: {$user->id}]", [
            'admin_id' => auth()->id(),
            'email' => $user->email,
            'mfa_enabled' => $user->mfa_enabled,
            'mfa_method' => $user->mfa_method,
            'usertype' => $user->usertype,
        ]);

        return redirect()->route('profile.admin.manageuser')->with('success', 'User updated successfully.');

    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->usertype === 'admin') {
            return redirect()->route('profile.admin.manageuser')
                ->with('error', 'Cannot delete an admin user.');
        }
        $user->delete();
        LoggingService::logSecurityViolation("Admin deleted user [ID: {$user->id}]", [
            'admin_id' => auth()->id(),
            'email' => $user->email,
        ]);


        return redirect()->route('profile.admin.manageuser')->with('success', 'User deleted successfully.');
    }
    public function ban(User $user)
    {
        if ($user->usertype === 'admin') {
            return response()->json(["success" => false, "message" => "Cannot ban an admin user."]);
        }
        if ($user->banned_status) {
            return response()->json(["success" => false, "message" => "User is already banned."]);
        }

        $user->banned_status = 1; // Ban user
        $user->save();
        LoggingService::logSecurityViolation("Admin banned user [ID: {$user->id}]", [
            'admin_id' => auth()->id(),
            'email' => $user->email,
        ]);


        return response()->json([
            "success" => true,
            "message" => "User has been banned successfully."
        ]);
    }

    public function unban(User $user)
    {
        if (!$user->banned_status) {
            return response()->json(["success" => false, "message" => "User is not banned."]);
        }

        $user->banned_status = 0; // Unban user
        $user->save();



        return response()->json([
            "success" => true,
            "message" => "User has been unbanned successfully."
        ]);
    }
    private function getSessionData($userId)
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent ?? '');

                $device = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');

                $loginTime = Carbon::parse($session->last_activity)->setTimezone('Asia/Jakarta');
                $expiresAt = $loginTime->copy()->addMinutes(config('session.lifetime'));

                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address ?? 'N/A',
                    'user_agent' => $session->user_agent ?? 'N/A',
                    'os' => $agent->platform() ?? 'N/A',
                    'browser' => $agent->browser() ?? 'N/A',
                    'device' => $device,
                    'login_time' => $loginTime->format('d M Y H:i'),
                    'expires_at' => $expiresAt->format('d M Y H:i'),
                ];
            });
    }



}
