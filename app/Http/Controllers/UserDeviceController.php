<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use App\Models\TrustedDevice;

class UserDeviceController extends Controller
{
    private function normalizeOS($os)
    {
        $osMappings = [
            'Windows NT 10.0' => 'Windows',
            'Windows NT 6.3' => 'Windows',
            'Windows NT 6.2' => 'Windows',
            'Windows NT 6.1' => 'Windows',
            'Windows NT 6.0' => 'Windows',
            'Windows NT 5.1' => 'Windows',
            'Macintosh' => 'MacOS',
            'X11; Linux' => 'Linux',
            'X11; Ubuntu' => 'Linux',
            'X11; Fedora' => 'Linux',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS'
        ];

        foreach ($osMappings as $key => $value) {
            if (stripos($os, $key) !== false) {
                return $value;
            }
        }

        return 'Unknown';
    }

    private function getUserDevices($userId)
    {
        return TrustedDevice::where('user_id', $userId)->get();
    }

    private function handleDeviceTracking($userId)
    {
        $currentIp = request()->ip();
        $agent = new Agent();
        $agent->setUserAgent(request()->header('User-Agent'));

        $deviceType = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');
        $os = $this->normalizeOS($agent->platform() ?? 'Unknown');
        $now = Carbon::now('Asia/Jakarta');

        // Auto-delete devices not used for 30 days
        TrustedDevice::where('user_id', $userId)
            ->where('updated_at', '<', $now->subDays(30))
            ->delete();

        $existingDevice = TrustedDevice::where('user_id', $userId)
            ->where('os', $os)
            ->first();

        if (!$existingDevice) {
            // Ensure the user has at most 3 trusted OS entries
            $osCount = TrustedDevice::where('user_id', $userId)->count();
            if ($osCount >= 1) {
                return redirect()->back()->with('error', 'You can only trust up to 3 operating systems. Remove one first.');
            }

            TrustedDevice::create([
                'user_id' => $userId,
                'ip_address' => $currentIp, // Stored but not used for trust
                'device' => $deviceType,
                'os' => $os,
                'trusted' => false,
                'action' => null,
                'updated_at' => $now,
            ]);
        } else {
            $existingDevice->touch();
        }
    }

    private function deleteDevice($id, $userId)
    {
        $device = TrustedDevice::where('id', $id)->where('user_id', $userId)->first();
        if (!$device)
            return false;

        $currentOs = $this->normalizeOS((new Agent())->platform());
        $device->delete();

        if ($device->os === $currentOs) {
            auth()->logout();
            return redirect('/login')->with('info', 'Your OS was removed, and you have been logged out.');
        }
        return true;
    }

    private function trustDevice($id, $userId)
    {
        $device = TrustedDevice::where('id', $id)->where('user_id', $userId)->first();
        if (!$device)
            return false;

        // Ensure the user has at most 3 trusted OS entries
        $osCount = TrustedDevice::where('user_id', $userId)->where('trusted', true)->count();
        if ($osCount >= 3) {
            return redirect()->back()->with('error', 'You can only trust up to 3 operating systems. Remove one first.');
        }

        // Trust the selected OS
        $device->update(['trusted' => true, 'action' => 'trust']);
    }

    private function untrustDevice($id, $userId)
    {
        TrustedDevice::where('id', $id)->where('user_id', $userId)->update(['trusted' => false, 'action' => 'untrust']);
    }

    public function Adminindex()
    {
        $userId = Auth::id();
        $this->handleDeviceTracking($userId);
        return view('profile.admin.mfa-setting', ['devices' => $this->getUserDevices($userId)]);
    }
    public function Admindelete($id)
    {
        return $this->deleteDevice($id, Auth::id()) ? redirect()->back()->with('success', 'Device removed.') : redirect()->back()->with('error', 'Device not found.');
    }
    public function Admintrust($id)
    {
        $this->trustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is now trusted.');
    }
    public function Adminuntrust($id)
    {
        $this->untrustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is no longer trusted.');
    }

    public function Studentindex()
    {
        $userId = Auth::id();
        $this->handleDeviceTracking($userId);
        return view('profile.student.mfa-setting', ['devices' => $this->getUserDevices($userId)]);
    }
    public function Studentdelete($id)
    {
        return $this->deleteDevice($id, Auth::id()) ? redirect()->back()->with('success', 'Device removed.') : redirect()->back()->with('error', 'Device not found.');
    }
    public function Studenttrust($id)
    {
        $this->trustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is now trusted.');
    }
    public function Studentuntrust($id)
    {
        $this->untrustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is no longer trusted.');
    }

    public function Staffindex()
    {
        $userId = Auth::id();
        $this->handleDeviceTracking($userId);
        return view('profile.staff.mfa-setting', ['devices' => $this->getUserDevices($userId)]);
    }
    public function Staffdelete($id)
    {
        return $this->deleteDevice($id, Auth::id()) ? redirect()->back()->with('success', 'Device removed.') : redirect()->back()->with('error', 'Device not found.');
    }
    public function Stafftrust($id)
    {
        $this->trustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is now trusted.');
    }
    public function Staffuntrust($id)
    {
        $this->untrustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is no longer trusted.');
    }

    public function Generalindex()
    {
        $userId = Auth::id();
        $this->handleDeviceTracking($userId);
        return view('profile.mfa-setting', ['devices' => $this->getUserDevices($userId)]);
    }
    public function Generaldelete($id)
    {
        return $this->deleteDevice($id, Auth::id()) ? redirect()->back()->with('success', 'Device removed.') : redirect()->back()->with('error', 'Device not found.');
    }
    public function Generaltrust($id)
    {
        $this->trustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is now trusted.');
    }
    public function Generaluntrust($id)
    {
        $this->untrustDevice($id, Auth::id());
        return redirect()->back()->with('success', 'OS is no longer trusted.');
    }
}


