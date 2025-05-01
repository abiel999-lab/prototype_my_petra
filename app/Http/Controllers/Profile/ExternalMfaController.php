<?php

namespace App\Http\Controllers\Profile;

use App\Models\User;
use App\Models\TrustedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Services\WhatsAppService;
use App\Services\SmsService;
use PragmaRX\Google2FA\Google2FA;
use App\Mail\ViolationMail;
use App\Services\LoggingService;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class ExternalMfaController extends Controller
{
    public function handle(Request $request)
    {
        $userId = session('pending_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['message' => 'Unauthorized access.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['message' => 'User not found.']);
        }

        if ($user->mfa && $user->mfa->mfa_enabled) {
            $this->sendOtpIfNeeded($user);

            return view('auth.external.mfa-challenge-external', [
                'redirect' => $request->query('redirect'),
            ]);
        }

        // No MFA enabled → show MFA setup
        $view = match ($user->usertype) {
            'admin' => 'profile.external.admin.mfa-setting-external',
            'student' => 'profile.external.student.mfa-setting-external',
            'staff' => 'profile.external.staff.mfa-setting-external',
            default => 'profile.external.mfa-setting-external',
        };

        $devices = TrustedDevice::where('user_id', $user->id)->get();

        return view($view, [
            'redirect' => $request->query('redirect'),
            'devices' => $devices
        ]);
    }

    private function sendOtpIfNeeded($user)
    {
        $now = now();

        if ($user->mfa->two_factor_code && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {
            return; // Skip if OTP is still valid
        }

        $code = $this->generateOtpWithNumber();
        $user->mfa->two_factor_code = Crypt::encryptString($code);
        $user->mfa->otp_expires_at = $now->addMinutes(5);
        $user->failed_otp_attempts = 0;
        $user->mfa->save();
        $user->save();
        LoggingService::logMfaEvent("Generated OTP for User [ID: {$user->id}]", [
            'method' => $user->mfa->mfa_method,
        ]);


        try {
            switch ($user->mfa->mfa_method) {
                case 'email':
                    Mail::to($user->email)->send(new OtpMail($code));
                    LoggingService::logMfaEvent("OTP email sent to {$user->email}");
                    break;

                case 'whatsapp':
                    (new WhatsAppService())->sendOtp($user->phone_number, $code);
                    LoggingService::logMfaEvent("WhatsApp OTP sent to {$user->phone_number}");
                    break;

                case 'sms':
                    (new SmsService())->sendSms($user->phone_number, "Your OTP Code is: $code", 'OTP');
                    LoggingService::logMfaEvent("SMS OTP sent to {$user->phone_number}");
                    break;

                case 'google_auth':
                    // No OTP sent via backend, handled on-device
                    break;

                default:
                    LoggingService::logSecurityViolation("Unhandled MFA method: {$user->mfa->mfa_method} for user ID: {$user->id}");
            }
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("OTP delivery failed for User [ID: {$user->id}]: " . $e->getMessage());
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session('pending_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['message' => 'Unauthorized access.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['message' => 'User not found.']);
        }

        // 🚨 Lock out immediately if 10 or more failed attempts
        if ($user->failed_otp_attempts >= 10) {
            return redirect()->route('mfa-challenge-external')->with(['otp_failed_limit' => true]);
        }


        // ✅ Check OTP
        if ($user->mfa && $this->validateOtp($user, $request->code)) {

            session(['two_factor_authenticated' => true]);
            session()->forget('pending_user_id');

            $user->mfa->update([
                'two_factor_code' => null,
                'otp_expires_at' => null,
            ]);
            $user->failed_otp_attempts = 0;
            $user->save();

            return redirect()->to($request->input('redirect', route('dashboard')));
        }

        // ❌ OTP incorrect → increase attempts
        $user->increment('failed_otp_attempts');

        $user->save();

        // 🚨 If exactly 10 failed attempts, trigger email alert
        if ($user->failed_otp_attempts == 10) {
            LoggingService::logSecurityViolation("User [ID: {$user->id}] locked out after 10 OTP failures (MFA method: {$user->mfa->mfa_method})");
            Mail::to('mfa.mypetra@petra.ac.id')->send(new ViolationMail($user, 'otp'));
            return redirect()->route('mfa-challenge-external')->with(['otp_failed_limit' => true]);
        }

        return redirect()->route('mfa-challenge-external')->withErrors([
            'code' => "Incorrect OTP. You have " . (10 - $user->failed_otp_attempts) . " attempts left."
        ]);
    }

    private function validateOtp($user, $code)
    {
        $now = now();

        if ($user->mfa->mfa_method === 'email') {
            try {
                $decryptedOtp = Crypt::decryptString($user->mfa->two_factor_code);

                // Periksa apakah OTP cocok dan belum kedaluwarsa
                if ($code == $decryptedOtp && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false; // Jika gagal dekripsi, anggap OTP tidak valid
            }
        }

        if ($user->mfa->mfa_method === 'google_auth') {
            $google2fa = new Google2FA();
            return $google2fa->verifyKey($user->mfa->google2fa_secret, $code);
        }
        if ($user->mfa->mfa_method === 'whatsapp') {
            try {
                $decryptedOtp = Crypt::decryptString($user->mfa->two_factor_code);

                if ($code == $decryptedOtp && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        if ($user->mfa->mfa_method === 'sms') {
            try {
                $decryptedOtp = Crypt::decryptString($user->mfa->two_factor_code);

                if ($code == $decryptedOtp && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }


        return false;
    }
    private function generateOtpWithNumber($length = 6)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        do {
            $otp = '';
            for ($i = 0; $i < $length; $i++) {
                $otp .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (!preg_match('/[A-Z]/', $otp) || !preg_match('/[a-z]/', $otp) || !preg_match('/\d/', $otp));
        return $otp;
    }
}
