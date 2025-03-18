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
        if (!$os || $os === 'Unknown') {
            return 'Unknown';
        }

        $osMappings = [
            'Windows NT' => 'Windows',
            'Windows' => 'Windows',
            'Mac' => 'MacOS',
            'Macintosh' => 'MacOS',
            'iOS' => 'iOS',
            'Android' => 'Android',
            'Linux' => 'Linux',
            'Ubuntu' => 'Linux',
            'Fedora' => 'Linux',
            'X11' => 'Linux',
            'Debian' => 'Linux',
            'Chrome OS' => 'ChromeOS'
        ];

        foreach ($osMappings as $key => $value) {
            if (stripos($os, $key) !== false) {
                return $value;
            }
        }

        return $os; // Instead of returning "Unknown", return the detected OS if it exists.
    }


    private function getUserDevices($userId)
    {
        return TrustedDevice::where('user_id', $userId)->get();
    }

    private function handleDeviceTracking($userId)
    {
        $currentIp = request()->ip();
        $agent = new Agent();
        $userAgent = request()->header('User-Agent');

        // Log the User-Agent string for debugging
        \Log::info('User-Agent: ' . $userAgent);

        // Set User-Agent for detection
        $agent->setUserAgent($userAgent);

        // Detect OS
        $detectedOS = $agent->platform() ?? 'Unknown';
        \Log::info('Detected OS before normalization: ' . $detectedOS);

        // Normalize OS name
        $os = $this->normalizeOS($detectedOS);
        \Log::info('Normalized OS: ' . $os);

        $deviceType = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');

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
            if ($osCount >= 3) {
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
        $device = TrustedDevice::findOrFail($id);
        $userId = $device->user_id;

        // Untrust all other devices for the user
        TrustedDevice::where('user_id', $userId)->update(['trusted' => false]);

        // Trust the selected device
        $device->update(['trusted' => true]);

        return redirect()->back()->with('success', 'Only one device can be trusted. Other devices have been untrusted.');
    }

    public function Adminuntrust($id)
    {
        $device = TrustedDevice::findOrFail($id);
        $device->update(['trusted' => false]);

        return redirect()->back()->with('success', 'This device is no longer trusted.');
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


