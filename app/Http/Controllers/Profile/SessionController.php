<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use App\Services\LoggingService;
use App\Http\Controllers\Controller;

class SessionController extends Controller
{
    /**
     * Ambil data session user tertentu.
     */
    public function getSessionData($userId)
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);

                $device = $agent->isDesktop()
                    ? 'Desktop'
                    : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');

                // Convert last activity ke timezone Jakarta (WIB)
                $lastActivity = Carbon::parse($session->last_activity)->setTimezone('Asia/Jakarta');

                // Fix session time handling
                $loginTime = session('login_time')
                    ? Carbon::parse(session('login_time'))->setTimezone('Asia/Jakarta')
                    : $lastActivity;

                $expiresAt = session('expires_at')
                    ? Carbon::parse(session('expires_at'))->setTimezone('Asia/Jakarta')
                    : $lastActivity->copy()->addMinutes(intval(config('session.lifetime')));

                return (object) [
                    'id'          => $session->id,
                    'ip_address'  => $session->ip_address,
                    'user_agent'  => $session->user_agent,
                    'os'          => $agent->platform() ?? 'Unknown',
                    'browser'     => $agent->browser() ?? 'Unknown',
                    'device'      => $device,
                    'login_time'  => $loginTime->format('d M Y H:i'),
                    'expires_at'  => $expiresAt->format('d M Y H:i'),
                ];
            });
    }

    // ---------------- USER SESSION MANAGEMENT ----------------

    public function show()
    {
        $sessions = $this->getSessionData(Auth::id());
        return view('profile.session', compact('sessions')); // Corrected view path
    }

    public function revoke(Request $request, $id)
    {
        return $this->revokeSession($request, $id, 'profile.session.show');
    }

    public function revokeAll(Request $request)
    {
        return $this->revokeAllSessions($request);
    }

    // ---------------- ADMIN SESSION MANAGEMENT ----------------

    public function Adminshow()
    {
        $sessions = $this->getSessionData(Auth::id());
        return view('profile.admin.session', compact('sessions'));
    }

    public function Adminrevoke(Request $request, $id)
    {
        return $this->revokeSession($request, $id, 'profile.admin.session.show');
    }

    public function AdminrevokeAll(Request $request)
    {
        return $this->revokeAllSessions($request);
    }

    // ---------------- STUDENT SESSION MANAGEMENT ----------------

    public function Studentshow()
    {
        $sessions = $this->getSessionData(Auth::id());
        return view('profile.student.session', compact('sessions'));
    }

    public function Studentrevoke(Request $request, $id)
    {
        return $this->revokeSession($request, $id, 'profile.student.session.show');
    }

    public function StudentrevokeAll(Request $request)
    {
        return $this->revokeAllSessions($request);
    }

    // ---------------- STAFF SESSION MANAGEMENT ----------------

    public function Staffshow()
    {
        $sessions = $this->getSessionData(Auth::id());
        return view('profile.staff.session', compact('sessions'));
    }

    public function Staffrevoke(Request $request, $id)
    {
        return $this->revokeSession($request, $id, 'profile.staff.session.show');
    }

    public function StaffrevokeAll(Request $request)
    {
        return $this->revokeAllSessions($request);
    }

    // ---------------- HELPER FUNCTIONS ----------------

    /**
     * Revoke satu session.
     * Kalau session yang dicabut adalah session saat ini -> langsung logout.
     */
    private function revokeSession(Request $request, $id, $route)
    {
        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();

        $session = DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$session) {
            return redirect()->route($route)->with('error', 'Sesi tidak ditemukan atau tidak diizinkan.');
        }

        // Hapus session di database
        DB::table('sessions')->where('id', $id)->delete();

        LoggingService::logMfaEvent("User [ID: {$userId}] revoked a session", [
            'session_id' => $id,
        ]);

        // Jika session yang dihapus adalah session yang sedang digunakan sekarang
        if ($id === $currentSessionId) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Sesi Anda berhasil dicabut. Silakan login kembali.');
        }

        // Kalau yang dihapus adalah session lain (device lain), tetap di halaman sekarang
        return redirect()->route($route)->with('success', 'Sesi berhasil dicabut.');
    }

    /**
     * Revoke semua session user saat ini.
     * Termasuk session sekarang -> otomatis logout.
     */
    private function revokeAllSessions(Request $request)
    {
        $userId = Auth::id();

        DB::table('sessions')->where('user_id', $userId)->delete();

        LoggingService::logMfaEvent("User [ID: {$userId}] revoked all active sessions");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Semua sesi Anda telah dicabut. Silakan login kembali.');
    }

    /**
     * Admin revoke session dari halaman manage user.
     * Kalau admin tanpa sengaja hapus session miliknya sendiri, dia juga akan logout.
     */
    public function AdminRevokeFromManageUser(Request $request, $id)
    {
        // Hanya admin boleh akses
        if (!Auth::check() || Auth::user()->usertype !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $session = DB::table('sessions')->where('id', $id)->first();

        if (!$session) {
            return back()->with('error', 'Session not found.');
        }

        $currentSessionId = $request->session()->getId();

        DB::table('sessions')->where('id', $id)->delete();

        LoggingService::logMfaEvent("Admin revoked session ID: $id", [
            'admin_id' => Auth::id(),
            'user_id'  => $session->user_id,
            'ip'       => $session->ip_address,
        ]);

        // Jika admin menghapus session-nya sendiri -> logout
        if ($id === $currentSessionId) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Session Anda sendiri telah dicabut. Silakan login kembali.');
        }

        return back()->with('success', 'Session revoked successfully.');
    }
}
