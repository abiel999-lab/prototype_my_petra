<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Mfa;
use App\Services\LoggingService;
use PragmaRX\Google2FA\Google2FA;

class ExtendedMfaController extends Controller
{
    public function showChallenge()
    {
        $user = Auth::user();
        $mfa = $user->mfa;

        if (!$mfa || !$mfa->extended_mfa_enabled) {
            abort(403, 'Extended MFA is not enabled.');
        }

        // ✅ Generate OTP jika metode WhatsApp
        if ($mfa->extended_mfa_method === 'whatsapp') {
            $code = $this->generateSecureOtp();
            $mfa->two_factor_code = $code;
            $mfa->otp_expires_at = now()->addMinutes(5);
            $mfa->save();

            app(\App\Services\WhatsAppService::class)->sendOtp($user->phone_number, $code);
        }

        return view('auth.extendedmfa-challenge', compact('mfa'));
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string'
        ]);

        $user = Auth::user();
        $mfa = $user->mfa;
        $appKey = session('mfa_app_key'); // ⬅️ Ambil key dari middleware

        if (!$mfa || !$mfa->extended_mfa_enabled) {
            abort(403, 'Extended MFA is not enabled.');
        }

        if ($mfa->extended_mfa_method === 'google_auth') {
            $google2fa = app('pragmarx.google2fa');
            if ($google2fa->verifyKey($mfa->google2fa_secret, $request->otp_code)) {
                Session::put("extended_mfa_verified.$appKey", true); // ⬅️ per-app
                Session::forget('mfa_app_key');
                return redirect()->intended();
            }
        }

        if (
            $mfa->extended_mfa_method === 'whatsapp' &&
            $request->otp_code === $mfa->two_factor_code &&
            now()->lt($mfa->otp_expires_at)
        ) {
            $mfa->two_factor_code = null;
            $mfa->otp_expires_at = null;
            $mfa->save();

            Session::put("extended_mfa_verified.$appKey", true); // ⬅️ per-app
            Session::forget('mfa_app_key');
            return redirect()->intended();
        }

        return back()->withErrors(['otp_code' => 'Invalid or expired OTP.']);
    }


    public function resend(Request $request)
    {
        $user = Auth::user();
        $mfa = $user->mfa;

        if ($mfa && $mfa->extended_mfa_enabled && $mfa->extended_mfa_method === 'whatsapp') {
            $code = $this->generateSecureOtp();
            $mfa->two_factor_code = $code;
            $mfa->otp_expires_at = now()->addMinutes(5);
            $mfa->save();

            app(\App\Services\WhatsAppService::class)->sendOtp($user->phone_number, $code);
        }

        return response()->json(['status' => 'resent']);
    }

    public function cancel()
    {
        $appKey = session('mfa_app_key');
        Session::forget("extended_mfa_verified.$appKey");
        Session::forget('mfa_app_key');

        $user = Auth::user();
        $role = session('active_role', $user->usertype);

        return redirect()->route(match ($role) {
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            'student' => 'student.dashboard',
            default => 'dashboard',
        })->with('message', 'Extended MFA was canceled.');
    }



    private function saveExtendedSetting(Request $request)
    {
        $request->validate([
            'extended_mfa_enabled' => 'required|boolean',
            'extended_mfa_method' => 'required|in:google_auth,whatsapp',
        ]);

        $user = Auth::user();
        $mfa = $user->mfa;

        if (!$mfa) {
            return response()->json(['message' => 'MFA record not found'], 404);
        }

        // ⛔ Cek nomor HP jika WhatsApp diaktifkan
        if ($request->extended_mfa_enabled && $request->extended_mfa_method === 'whatsapp') {
            if (empty($user->phone_number)) {
                return response()->json([
                    'message' => 'Phone number is not set. Please update the mobile number in your profile.'
                ], 422);
            }
        }

        $mfa->extended_mfa_enabled = $request->extended_mfa_enabled;
        $mfa->extended_mfa_method = $request->extended_mfa_method;
        $mfa->save();

        return response()->json(['message' => 'Extended MFA setting saved']);
    }

    public function updateSetting(Request $request)
    {
        return $this->saveExtendedSetting($request);
    }

    public function updateSettingAdmin(Request $request)
    {
        return $this->saveExtendedSetting($request);
    }

    public function updateSettingStaff(Request $request)
    {
        return $this->saveExtendedSetting($request);
    }

    public function updateSettingStudent(Request $request)
    {
        return $this->saveExtendedSetting($request);
    }

    private function generateSecureOtp($length = 6)
    {
        do {
            $otp = substr(
                str_shuffle(
                    Str::random(2) . // huruf campuran
                    strtoupper(Str::random(2)) . // huruf besar
                    rand(10, 99) // angka
                ),
                0,
                $length
            );
        } while (
            !preg_match('/[A-Z]/', $otp) ||
            !preg_match('/[a-z]/', $otp) ||
            !preg_match('/[0-9]/', $otp)
        );

        return $otp;
    }

}
