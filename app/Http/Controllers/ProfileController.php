<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function studentprofile(Request $request): View
    {
        $user = $request->user();
        $sessionController = new SessionController();
        $sessions = $sessionController->getSessionData($user->id);
        $UserDevuceController = new UserDeviceController();
        $devices = $UserDevuceController->getUserDevices($user->id);
        return view('profile.student.setting', [
            'user' => $request->user(),
            'sessions' => $sessions,
            'devices' => $devices,
        ]);
    }
    public function staffprofile(Request $request): View
    {
        $user = $request->user();
        $sessionController = new SessionController();
        $sessions = $sessionController->getSessionData($user->id);
        $UserDevuceController = new UserDeviceController();
        $devices = $UserDevuceController->getUserDevices($user->id);
        return view('profile.staff.setting', [
            'user' => $request->user(),
            'sessions' => $sessions,
            'devices' => $devices,
        ]);
    }
    public function adminprofile(Request $request): View
    {
        $user = $request->user();
        $sessionController = new SessionController();
        $sessions = $sessionController->getSessionData($user->id);
        $UserDevuceController = new UserDeviceController();
        $devices = $UserDevuceController->getUserDevices($user->id);
        return view('profile.admin.setting', [
            'user' => $request->user(),
            'sessions' => $sessions,
            'devices' => $devices,
        ]);
    }
    public function profile(Request $request): View
    {
        $user = $request->user();
        $sessionController = new SessionController();
        $sessions = $sessionController->getSessionData($user->id);
        $UserDevuceController = new UserDeviceController();
        $devices = $UserDevuceController->getUserDevices($user->id);
        return view('profile.setting', [
            'user' => $request->user(),
            'sessions' => $sessions, // Make sure this is being passed correctly
            'devices' => $devices,
        ]);
    }
    public function studenteditprofile(Request $request): View
    {
        return view('profile.student.profile', [
            'user' => $request->user(),
        ]);
    }
    public function staffeditprofile(Request $request): View
    {
        return view('profile.staff.profile', [
            'user' => $request->user(),
        ]);
    }
    public function admineditprofile(Request $request): View
    {
        return view('profile.admin.profile', [
            'user' => $request->user(),
        ]);
    }
    public function editprofile(Request $request): View
    {

        return view('profile.profile', [
            'user' => $request->user(),
        ]);
    }
    public function studentsession(Request $request): View
    {
        return view('profile.student.session', [
            'user' => $request->user(),
        ]);
    }
    public function staffsession(Request $request): View
    {
        return view('profile.staff.session', [
            'user' => $request->user(),
        ]);
    }
    public function adminsession(Request $request): View
    {
        return view('profile.admin.session', [
            'user' => $request->user(),
        ]);
    }
    public function editsession(Request $request): View
    {
        return view('profile.session', [
            'user' => $request->user(),
        ]);
    }
    public function studentmfasetting(Request $request): View
    {
        return view('profile.student.mfa-setting', [
            'user' => $request->user(),
        ]);
    }
    public function staffmfasetting(Request $request): View
    {
        return view('profile.staff.mfa-setting', [
            'user' => $request->user(),
        ]);
    }
    public function adminmfasetting(Request $request): View
    {
        return view('profile.admin.mfa-setting', [
            'user' => $request->user(),
        ]);
    }
    public function mfasetting(Request $request): View
    {
        return view('profile.mfa-setting', [
            'user' => $request->user(),
        ]);
    }
    public function manageuser(Request $request): View
    {
        return view('profile.admin.manage-user', [
            'user' => $request->user(),
        ]);
    }

    // external function
// External MFA Settings for Student
    public function studentmfasettingexternal(Request $request): View
    {
        $user = $request->user();
        $deviceController = new UserDeviceController();
        $devices = $deviceController->getUserDevices($user->id);
        return view('profile.external.student.mfa-setting-external', [
            'user' => $user,
            'devices' => $devices,
        ]);
    }

    // External MFA Settings for Staff
    public function staffmfasettingexternal(Request $request): View
    {
        $user = $request->user();
        $deviceController = new UserDeviceController();
        $devices = $deviceController->getUserDevices($user->id);
        return view('profile.external.staff.mfa-setting-external', [
            'user' => $user,
            'devices' => $devices,
        ]);
    }

    // External MFA Settings for Admin
    public function adminmfasettingexternal(Request $request): View
    {
        $user = $request->user();
        $deviceController = new UserDeviceController();
        $devices = $deviceController->getUserDevices($user->id);
        return view('profile.external.admin.mfa-setting-external', [
            'user' => $user,
            'devices' => $devices,
        ]);
    }

    // External MFA Settings for General Users
    public function mfasettingexternal(Request $request): View
    {
        $user = $request->user();
        $deviceController = new UserDeviceController();
        $devices = $deviceController->getUserDevices($user->id);
        return view('profile.external.mfa-setting-external', [
            'user' => $user,
            'devices' => $devices,
        ]);
    }


    public function toggleMfa(Request $request)
    {
        $user = auth()->user();
        $user->mfa_enabled = !$user->mfa_enabled;
        $user->save();

        return response()->json([
            'status' => 'success',
            'mfa_enabled' => $user->mfa_enabled,
        ]);
    }
    public function setMfaMethod(Request $request)
    {
        $request->validate([
            'mfa_method' => 'required|in:email,google_auth,whatsapp,sms',
        ]);

        $user = auth()->user();
        $user->mfa_method = $request->mfa_method;

        // Jika memilih Google Authenticator, buat QR Code
        $qrCodeUrl = null;
        if ($user->mfa_method === 'google_auth') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();

            if (!$user->google2fa_secret) {
                $user->google2fa_secret = $google2fa->generateSecretKey();
            }

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $user->google2fa_secret
            );
        }

        // Simpan nomor HP jika memilih SMS
        if ($user->mfa_method === 'whatsapp' || $user->mfa_method === 'sms') {
            if (!$user->phone_number) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nomor HP belum diatur. Silakan update nomor HP di profil.',
                ], 400);
            }
        }


        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'MFA method updated successfully.',
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }


    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'regex:/^[0-9]{10,15}$/'],
        ]);

        auth()->user()->update([
            'phone_number' => $request->phone_number,
        ]);

        return back()->with('success', 'Nomor HP berhasil diperbarui!');
    }

}
