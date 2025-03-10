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
        return view('profile.student.setting', [
            'user' => $request->user(),
        ]);
    }
    public function staffprofile(Request $request): View
    {
        return view('profile.staff.setting', [
            'user' => $request->user(),
        ]);
    }
    public function adminprofile(Request $request): View
    {
        return view('profile.admin.setting', [
            'user' => $request->user(),
        ]);
    }
    public function profile(Request $request): View
    {
        return view('profile.setting', [
            'user' => $request->user(),
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
            'mfa_method' => 'required|in:email,google_auth',
        ]);

        $user = auth()->user();
        $user->mfa_method = $request->mfa_method;

        // Handle Google Authenticator-specific logic
        $qrCodeUrl = null;
        if ($user->mfa_method === 'google_auth') {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();

            // Check if the user already has a secret, generate one if not
            if (!$user->google2fa_secret) {
                $user->google2fa_secret = $google2fa->generateSecretKey();
            }

            // Generate the QR code URL
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),      // App name
                $user->email,            // User email
                $user->google2fa_secret  // Secret key
            );
        }

        // Save changes to the database
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
            'phone_number' => 'required|string|max:15',
        ]);

        auth()->user()->update([
            'phone_number' => $request->phone_number,
        ]);

        return back()->with('success', 'Nomor HP berhasil diperbarui!');
    }

}
