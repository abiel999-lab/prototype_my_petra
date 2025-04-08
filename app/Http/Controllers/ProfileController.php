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
        $method = $request->input('mfa_method');
        $otp = $request->input('otp');
        $qrCodeUrl = null;

        // Handle Google Authenticator
        if ($method === 'google_auth') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();

            if (!$user->google2fa_secret) {
                $user->google2fa_secret = $google2fa->generateSecretKey();
                $user->save();

                $qrCodeUrl = $google2fa->getQRCodeUrl(
                    config('app.name'),
                    $user->email,
                    $user->google2fa_secret
                );



                return response()->json([
                    'status' => 'pending',
                    'message' => 'Scan QR code and enter OTP to activate Mobile Authenticator.',
                    'qrCodeUrl' => $qrCodeUrl,
                ]);
            }

            if ($otp) {
                $isValid = $google2fa->verifyKey($user->google2fa_secret, $otp);
                if ($isValid) {
                    $user->mfa_method = 'google_auth';
                    $user->save();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Mobile Authenticator activated successfully.',
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid OTP. Please try again.',
                    ], 422);
                }
            }

            // ✅ FIXED: return QR again when OTP belum dikirim tapi secret sudah ada
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $user->google2fa_secret
            );



            return response()->json([
                'status' => 'pending',
                'message' => 'Enter the OTP to verify Mobile Authenticator.',
                'qrCodeUrl' => $qrCodeUrl,
            ]);
        }

        // Handle WhatsApp & SMS validation
        if (in_array($method, ['whatsapp', 'sms']) && empty($user->phone_number)) {
            $message = $method === 'sms'
                ? 'Phone number is not set. Please update it in your profile. SMS is slow and less secure. We recommend using Email, Google Authenticator, or WhatsApp.'
                : 'Phone number is not set. Please update the mobile number in your profile.';

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 400);
        }

        // Set method for email / sms / whatsapp
        $user->mfa_method = $method;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'MFA method has been updated successfully.',
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

    public function studentTogglePasswordless(Request $request)
    {
        $user = auth()->user();
        if ($user->usertype !== 'student') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $user->passwordless_enabled = !$user->passwordless_enabled;
        $user->save();

        return response()->json([
            'status' => 'success',
            'passwordless_enabled' => $user->passwordless_enabled,
        ]);
    }

    public function staffTogglePasswordless(Request $request)
    {
        $user = auth()->user();
        if ($user->usertype !== 'staff') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $user->passwordless_enabled = !$user->passwordless_enabled;
        $user->save();

        return response()->json([
            'status' => 'success',
            'passwordless_enabled' => $user->passwordless_enabled,
        ]);
    }

    public function adminTogglePasswordless(Request $request)
    {
        $user = auth()->user();
        if ($user->usertype !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $user->passwordless_enabled = !$user->passwordless_enabled;
        $user->save();

        return response()->json([
            'status' => 'success',
            'passwordless_enabled' => $user->passwordless_enabled,
        ]);
    }

    public function generalTogglePasswordless(Request $request)
    {
        $user = auth()->user();
        if ($user->usertype !== 'general') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $user->passwordless_enabled = !$user->passwordless_enabled;
        $user->save();

        return response()->json([
            'status' => 'success',
            'passwordless_enabled' => $user->passwordless_enabled,
        ]);
    }



}
