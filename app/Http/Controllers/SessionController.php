<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class SessionController extends Controller
{
    /**
     * Retrieve session data for the authenticated user.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUserSessions()
    {
        return DB::table('sessions')
            ->where('user_id', Auth::id()) // 🔹 Ensures only the logged-in user's sessions are retrieved
            ->get()
            ->map(function ($session) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);

                // Determine device type
                $device = $agent->device();
                if ($agent->isDesktop() || $agent->browser() === 'Chrome' || $device === 'WebKit') {
                    $device = 'Desktop';
                } elseif ($agent->isMobile()) {
                    $device = 'Mobile';
                } elseif ($agent->isTablet()) {
                    $device = 'Tablet';
                }

                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'os' => $agent->platform(),
                    'browser' => $agent->browser(),
                    'device' => $device,
                    'login_time' => Carbon::createFromTimestamp((int) $session->last_activity)->format('d M Y H:i'),
                    'expires_at' => Carbon::createFromTimestamp((int) $session->last_activity)
                        ->addMinutes((int) config('session.lifetime'))
                        ->format('d M Y H:i'),
                ];
            });
    }

    /**
     * Show session page for the authenticated user.
     */
    public function show()
    {
        $sessions = $this->getUserSessions();
        return view('profile.session', compact('sessions'));
    }

    /**
     * Show session page (Admin).
     */
    public function Adminshow()
    {
        $sessions = $this->getUserSessions();
        return view('profile.admin.session', compact('sessions'));
    }

    /**
     * Show session page (Student).
     */
    public function Studentshow()
    {
        $sessions = $this->getUserSessions();
        return view('profile.student.session', compact('sessions'));
    }

    /**
     * Show session page (Staff).
     */
    public function Staffshow()
    {
        $sessions = $this->getUserSessions();
        return view('profile.staff.session', compact('sessions'));
    }

    /**
     * Revoke a single session if it belongs to the authenticated user.
     *
     * @param int $id
     */
    public function revoke($id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', Auth::id()) // 🔹 Ensures only the logged-in user's session is deleted
            ->delete();

        return redirect()->route('profile.session.show')->with('success', 'Session revoked.');
    }

    /**
     * Revoke all sessions for the authenticated user.
     */
    public function revokeAll()
    {
        DB::table('sessions')->where('user_id', Auth::id())->delete();
        return redirect()->route('profile.session.show')->with('success', 'All sessions revoked.');
    }

    /**
     * Revoke a single session (Admin).
     *
     * @param int $id
     */
    public function Adminrevoke($id)
    {
        return $this->revoke($id);
    }

    /**
     * Revoke all sessions (Admin).
     */
    public function AdminrevokeAll()
    {
        return $this->revokeAll();
    }

    /**
     * Revoke a single session (Student).
     *
     * @param int $id
     */
    public function Studentrevoke($id)
    {
        return $this->revoke($id);
    }

    /**
     * Revoke all sessions (Student).
     */
    public function StudentrevokeAll()
    {
        return $this->revokeAll();
    }

    /**
     * Revoke a single session (Staff).
     *
     * @param int $id
     */
    public function Staffrevoke($id)
    {
        return $this->revoke($id);
    }

    /**
     * Revoke all sessions (Staff).
     */
    public function StaffrevokeAll()
    {
        return $this->revokeAll();
    }
}
