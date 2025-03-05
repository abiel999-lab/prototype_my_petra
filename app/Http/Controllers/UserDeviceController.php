<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;

class UserDeviceController extends Controller
{
    // Admin Functions
    public function Adminindex()
    {
        return $this->getDevicesView('admin');
    }

    public function Admindelete($id)
    {
        return $this->deleteDevice('admin', $id);
    }

    public function Admintrust($id)
    {
        return $this->trustDevice('admin', $id);
    }

    public function Adminuntrust($id)
    {
        return $this->untrustDevice('admin', $id);
    }

    // Student Functions
    public function Studentindex()
    {
        return $this->getDevicesView('student');
    }

    public function Studentdelete($id)
    {
        return $this->deleteDevice('student', $id);
    }

    public function Studenttrust($id)
    {
        return $this->trustDevice('student', $id);
    }

    public function Studentuntrust($id)
    {
        return $this->untrustDevice('student', $id);
    }

    // Staff Functions
    public function Staffindex()
    {
        return $this->getDevicesView('staff');
    }

    public function Staffdelete($id)
    {
        return $this->deleteDevice('staff', $id);
    }

    public function Stafftrust($id)
    {
        return $this->trustDevice('staff', $id);
    }

    public function Staffuntrust($id)
    {
        return $this->untrustDevice('staff', $id);
    }

    // General User Functions (No Folder)
    public function Generalindex()
    {
        return $this->getDevicesView('');
    }

    public function Generaldelete($id)
    {
        return $this->deleteDevice('', $id);
    }

    public function Generaltrust($id)
    {
        return $this->trustDevice('', $id);
    }

    public function Generaluntrust($id)
    {
        return $this->untrustDevice('', $id);
    }

    // Helper Functions
    private function getDevicesView($usertype)
    {
        $userId = Auth::id();
        $viewPath = $usertype ? "profile.{$usertype}.mfa-setting" : "profile.mfa-setting";

        $devices = DB::table('sessions')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($session) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'os' => $agent->platform(),
                    'browser' => $agent->browser(),
                    'last_used' => Carbon::parse($session->last_activity)->format('d M Y H:i'),
                    'trusted' => $session->trusted ?? false,
                ];
            })
            ->unique('ip_address')
            ->values()
            ->take(3);

        return view($viewPath,compact('devices'));
    }

    private function deleteDevice($usertype, $id)
    {
        $routeName = $usertype ? "profile.{$usertype}.mfa" : "profile.mfa";
        DB::table('sessions')->where('id', $id)->delete();
        return redirect()->route($routeName)->with('success', 'Device removed.');
    }

    private function trustDevice($usertype, $id)
    {
        $userId = Auth::id();
        $routeName = $usertype ? "profile.{$usertype}.mfa" : "profile.mfa";

        $session = DB::table('sessions')->where('id', $id)->first();
        if (!$session) {
            return redirect()->route($routeName)->with('error', 'Device not found.');
        }

        $agent = new Agent();
        $agent->setUserAgent($session->user_agent);
        $newTrustedOS = $agent->platform();
        $newTrustedIP = $session->ip_address;

        DB::table('sessions')->where('user_id', $userId)->update(['trusted' => false]);
        DB::table('sessions')->where('id', $id)->update(['trusted' => true]);

        return redirect()->route($routeName)->with('success', "{$newTrustedOS} on IP {$newTrustedIP} is now the trusted device.");
    }

    private function untrustDevice($usertype, $id)
    {
        $routeName = $usertype ? "profile.{$usertype}.mfa" : "profile.mfa";

        $session = DB::table('sessions')->where('id', $id)->first();
        if (!$session) {
            return redirect()->route($routeName)->with('error', 'Device not found.');
        }

        DB::table('sessions')->where('id', $id)->update(['trusted' => false]);

        return redirect()->route($routeName)->with('success', 'Device is no longer trusted.');
    }
}
