<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionController extends Controller
{
    // general
    public function show()
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'os' => $this->getOS($session->user_agent), // Extract OS
                    'login_time' => Carbon::parse($session->last_activity)->format('d M Y H:i'),
                    'expires_at' => Carbon::parse($session->last_activity)
                        ->addMinutes((int) config('session.lifetime'))
                        ->format('d M Y H:i'),
                ];
            });

        return view('profile.session', compact('sessions'));
    }


    public function revoke($id)
    {
        DB::table('sessions')->where('id', $id)->delete();
        return redirect()->route('profile.session.show')->with('success', 'Session revoked.');
    }

    public function revokeAll()
    {
        DB::table('sessions')->where('user_id', Auth::id())->delete();
        return redirect()->route('profile.session.show')->with('success', 'All sessions revoked.');
    }

    // admin
    public function Adminshow()
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'os' => $this->getOS($session->user_agent), // Extract OS
                    'login_time' => Carbon::parse($session->last_activity)->format('d M Y H:i'),
                    'expires_at' => Carbon::parse($session->last_activity)
                        ->addMinutes((int) config('session.lifetime'))
                        ->format('d M Y H:i'),
                ];
            });

        return view('profile.admin.session', compact('sessions'));
    }


    public function Adminrevoke($id)
    {
        DB::table('sessions')->where('id', $id)->delete();
        return redirect()->route('profile.admin.session.show')->with('success', 'Session revoked.');
    }

    public function AdminrevokeAll()
    {
        DB::table('sessions')->where('user_id', Auth::id())->delete();
        return redirect()->route('profile.admin.session.show')->with('success', 'All sessions revoked.');
    }

    // student
    public function Studentshow()
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'os' => $this->getOS($session->user_agent), // Extract OS
                    'login_time' => Carbon::parse($session->last_activity)->format('d M Y H:i'),
                    'expires_at' => Carbon::parse($session->last_activity)
                        ->addMinutes((int) config('session.lifetime'))
                        ->format('d M Y H:i'),
                ];
            });

        return view('profile.student.session', compact('sessions'));
    }


    public function Studentrevoke($id)
    {
        DB::table('sessions')->where('id', $id)->delete();
        return redirect()->route('profile.student.session.show')->with('success', 'Session revoked.');
    }

    public function StudentrevokeAll()
    {
        DB::table('sessions')->where('user_id', Auth::id())->delete();
        return redirect()->route('profile.student.session.show')->with('success', 'All sessions revoked.');
    }

    // staff
    public function Staffshow()
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'os' => $this->getOS($session->user_agent), // Extract OS
                    'login_time' => Carbon::parse($session->last_activity)->format('d M Y H:i'),
                    'expires_at' => Carbon::parse($session->last_activity)
                        ->addMinutes((int) config('session.lifetime'))
                        ->format('d M Y H:i'),
                ];
            });

        return view('profile.staff.session', compact('sessions'));
    }


    public function Staffrevoke($id)
    {
        DB::table('sessions')->where('id', $id)->delete();
        return redirect()->route('profile.staff.session.show')->with('success', 'Session revoked.');
    }

    public function StaffrevokeAll()
    {
        DB::table('sessions')->where('user_id', Auth::id())->delete();
        return redirect()->route('profile.staff.session.show')->with('success', 'All sessions revoked.');
    }

    private function getOS($userAgent)
    {
        if (preg_match('/windows nt 10/i', $userAgent)) {
            return "Windows 10";
        } elseif (preg_match('/windows nt 6.3/i', $userAgent)) {
            return "Windows 8.1";
        } elseif (preg_match('/windows nt 6.2/i', $userAgent)) {
            return "Windows 8";
        } elseif (preg_match('/windows nt 6.1/i', $userAgent)) {
            return "Windows 7";
        } elseif (preg_match('/windows nt 6.0/i', $userAgent)) {
            return "Windows Vista";
        } elseif (preg_match('/windows nt 5.1/i', $userAgent)) {
            return "Windows XP";
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return "Mac OS X";
        } elseif (preg_match('/linux/i', $userAgent)) {
            return "Linux";
        } elseif (preg_match('/android/i', $userAgent)) {
            return "Android";
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return "iOS";
        } else {
            return "Unknown OS";
        }
    }
}



