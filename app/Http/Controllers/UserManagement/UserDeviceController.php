<?php


namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Mail;
use App\Mail\MfaExternalMail;
use App\Services\LoggingService;
use App\Http\Controllers\Controller;

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


    public function getUserDevices($userId)
    {
        return TrustedDevice::where('user_id', $userId)->get();
    }

    public function handleDeviceTracking($userId)
    {
        $currentIp = request()->ip();
        $agent = new Agent();
        $userAgent = request()->header('User-Agent');

        $agent->setUserAgent($userAgent);

        $detectedOS = $agent->platform() ?? 'Unknown';
        $normalizedOS = $this->normalizeOS($detectedOS);
        $deviceType = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Phone' : 'Unknown');
        $now = Carbon::now('Asia/Jakarta');

        // 🧹 Auto-delete stale devices (30+ days unused)
        TrustedDevice::where('user_id', $userId)
            ->where('updated_at', '<', $now->subDays(30))
            ->delete();

        // 🔍 Check if OS already exists (regardless of IP/device)
        $existingDevice = TrustedDevice::where('user_id', $userId)
            ->where('os', $normalizedOS)
            ->first();

        if ($existingDevice) {
            // 🟢 OS already tracked → update timestamp
            $existingDevice->touch();
            return;
        }

        // 🔐 Count distinct OSes
        $distinctOSCount = TrustedDevice::where('user_id', $userId)
            ->select('os')
            ->distinct()
            ->count();

        // ⛔️ Block login if trying to use 4th OS
        if ($distinctOSCount >= 3) {
            session(['pending_user_id' => $userId]);
            LoggingService::logSecurityViolation("User [{$userId}] blocked from logging in with 4th OS (Detected: {$normalizedOS})", [
                'user_id' => $userId,
                'ip' => $currentIp,
                'os' => $normalizedOS,
            ]);

            return redirect()->route('device-limit-warning');
        }

        // ✅ Save new OS (as new device)
        TrustedDevice::create([
            'user_id' => $userId,
            'ip_address' => $currentIp,
            'device' => $deviceType,
            'os' => $normalizedOS,
            'trusted' => false,
            'action' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        LoggingService::logMfaEvent("New OS added", [
            'user_id' => $userId,
            'ip' => $currentIp,
            'device' => $deviceType,
            'os' => $normalizedOS,
        ]);
    }

    private function deleteDevice($id, $userId)
    {
        $device = TrustedDevice::where('id', $id)->where('user_id', $userId)->first();
        if (!$device)
            return false;

        $currentOs = $this->normalizeOS((new Agent())->platform());
        $device->delete();
        LoggingService::logMfaEvent("Device deleted", [
            'device_id' => $device->id,
            'user_id' => $userId,
            'os' => $device->os,
            'ip' => $device->ip_address,
        ]);


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
        LoggingService::logMfaEvent("Device untrusted", [
            'device_id' => $id,
            'user_id' => $userId,
        ]);

    }

    public function Adminindex()
    {
        $userId = Auth::id();
        $this->handleDeviceTracking($userId);
        return view('profile.admin.mfa-setting', ['devices' => $this->getUserDevices($userId)]);
    }
    public function Admindelete($id)
    {
        $device = TrustedDevice::findOrFail($id);
        $device->delete();

        return redirect()->back()->with('success', 'Device removed.');
    }

    public function Admintrust($id)
    {
        $device = TrustedDevice::findOrFail($id);
        $userId = $device->user_id;

        // Untrust all other devices for the user
        TrustedDevice::where('user_id', $userId)->update(['trusted' => false]);

        // Trust the selected device
        $device->update(['trusted' => true]);
        LoggingService::logMfaEvent("Trusted device updated", [
            'device_id' => $device->id,
            'user_id' => $userId,
            'os' => $device->os,
            'trusted' => true,
        ]);


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
    public function sendExternalEmailLink(Request $request)
    {
        // ✅ Ambil dari sesi
        $userId = session('pending_user_id');

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // ✅ Tentukan view MFA setting external berdasarkan role
        $externalMfaSettingRoute = match ($user->usertype) {
            'admin' => 'profile.external.admin.mfa-setting-external',
            'student' => 'profile.external.student.mfa-setting-external',
            'staff' => 'profile.external.staff.mfa-setting-external',
            default => 'profile.external.mfa-setting-external',
        };

        // ✅ Tentukan route MFA settings utama (setelah lolos OTP)
        $mfaSettingRoute = match ($user->usertype) {
            'admin' => route('profile.admin.mfa.external'),
            'student' => route('profile.student.mfa.external'),
            'staff' => route('profile.staff.mfa.external'),
            default => route('profile.mfa.external'),
        };

        // ✅ Jika MFA aktif, kirim link ke halaman verifikasi OTP external
        if ($user->mfa && $user->mfa->mfa_enabled) {
            $mfaChallengeUrl = route('mfa-challenge-external', ['redirect' => $mfaSettingRoute]);

            // Kirim link MFA Challenge External
            Mail::to($user->email)->send(new MfaExternalMail($mfaChallengeUrl));
            LoggingService::logMfaEvent("Sent MFA link to {$user->email}", [
                'redirect' => $mfaChallengeUrl,
            ]);


        } else {
            // MFA tidak aktif, langsung kirim link ke halaman pengaturan MFA
            Mail::to($user->email)->send(new MfaExternalMail($mfaSettingRoute));
            LoggingService::logMfaEvent("Sent MFA settings link to {$user->email}", [
                'redirect' => $mfaSettingRoute,
            ]);

        }

        return response()->json(['message' => 'Email sent']);
    }


}


