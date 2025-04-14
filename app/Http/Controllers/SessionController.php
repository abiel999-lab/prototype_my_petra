<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use App\Services\LoggingService;


class SessionController extends Controller
{
    public function getSessionData($userId)
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);

                $device = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');

                // Convert last activity to Jakarta timezone (WIB)
                $lastActivity = Carbon::parse($session->last_activity)->setTimezone('Asia/Jakarta');

                // Fix session time handling
                $loginTime = session('login_time')
                    ? Carbon::parse(session('login_time'))->setTimezone('Asia/Jakarta')
                    : $lastActivity;

                $expiresAt = session('expires_at')
                    ? Carbon::parse(session('expires_at'))->setTimezone('Asia/Jakarta')
                    : $lastActivity->copy()->addMinutes(intval(config('session.lifetime')));

                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'os' => $agent->platform() ?? 'Unknown',
                    'browser' => $agent->browser() ?? 'Unknown',
                    'device' => $device,
                    'login_time' => $loginTime->format('d M Y H:i'),
                    'expires_at' => $expiresAt->format('d M Y H:i'),
                ];
            });
    }


    public function show()
    {
        $sessions = $this->getSessionData(auth()->id());

        return view('profile.session', compact('sessions')); // Corrected view path
    }

    public function revoke($id)
    {
        return $this->revokeSession($id, 'profile.session.show');
    }

    public function revokeAll()
    {
        return $this->revokeAllSessions('profile.session.show');
    }

    // ---------------- ADMIN SESSION MANAGEMENT ----------------
    public function Adminshow()
    {
        $sessions = $this->getSessionData(auth()->id());
        return view('profile.admin.session', compact('sessions'));
    }



    public function Adminrevoke($id)
    {
        return $this->revokeSession($id, 'profile.admin.session.show');
    }

    public function AdminrevokeAll()
    {
        return $this->revokeAllSessions('profile.admin.session.show');
    }

    // ---------------- STUDENT SESSION MANAGEMENT ----------------
    public function Studentshow()
    {
        $sessions = $this->getSessionData(auth()->id());
        return view('profile.student.session', compact('sessions'));
    }

    public function Studentrevoke($id)
    {
        return $this->revokeSession($id, 'profile.student.session.show');
    }

    public function StudentrevokeAll()
    {
        return $this->revokeAllSessions('profile.student.session.show');
    }

    // ---------------- STAFF SESSION MANAGEMENT ----------------
    public function Staffshow()
    {
        $sessions = $this->getSessionData(auth()->id());
        return view('profile.staff.session', compact('sessions'));
    }



    public function Staffrevoke($id)
    {
        return $this->revokeSession($id, 'profile.staff.session.show');
    }

    public function StaffrevokeAll()
    {
        return $this->revokeAllSessions('profile.staff.session.show');
    }

    // ---------------- HELPER FUNCTIONS ----------------
    private function revokeSession($id, $route)
    {
        $userId = auth()->id();
        $session = DB::table('sessions')->where('id', $id)->where('user_id', $userId)->first();

        if (!$session) {
            return redirect()->route($route)->with('error', 'Sesi tidak ditemukan atau tidak diizinkan.');
        }

        DB::table('sessions')->where('id', $id)->delete();
        LoggingService::logMfaEvent("User [ID: {$userId}] revoked a session", [
            'session_id' => $id,
        ]);


        return redirect()->route($route)->with('success', 'Sesi berhasil dicabut.');
    }

    private function revokeAllSessions($route)
    {
        DB::table('sessions')->where('user_id', auth()->id())->delete();
        LoggingService::logMfaEvent("User [ID: " . auth()->id() . "] revoked all active sessions");

        return redirect()->route($route)->with('success', 'Semua sesi Anda telah dicabut.');
    }
    public function AdminRevokeFromManageUser($id)
    {
        // Hanya admin boleh akses
        if (auth()->user()->usertype !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $session = DB::table('sessions')->where('id', $id)->first();

        if (!$session) {
            return back()->with('error', 'Session not found.');
        }

        DB::table('sessions')->where('id', $id)->delete();

        LoggingService::logMfaEvent("Admin revoked session ID: $id", [
            'admin_id' => auth()->id(),
            'user_id' => $session->user_id,
            'ip' => $session->ip_address,
        ]);

        return back()->with('success', 'Session revoked successfully.');
    }



}
