<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use App\Mail\OtpMail;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Crypt;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use App\Mail\ViolationMail;
use App\Services\SmsService;
use App\Services\LoggingService;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class TwoFactorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $qrCodeUrl = null;
        $mfaMethod = optional($user->mfa)->mfa_method;
        if ($user->mfa && $user->mfa->mfa_method === 'email') {
            if (!$user->mfa->two_factor_code) {
                $this->handleEmailOtp($user);
            }
        } elseif ($user->mfa && $user->mfa->mfa_method === 'google_auth') {
            $qrCodeUrl = $this->generateGoogleQrCode($user);
        } elseif ($user->mfa && $user->mfa->mfa_method === 'whatsapp') {
            if (!$user->mfa->two_factor_code) {
                $this->handleWhatsAppOtp($user);
            }
        } elseif ($user->mfa && $user->mfa->mfa_method === 'sms') {
            if (!$user->mfa->two_factor_code) {
                $this->handleSmsOtp($user);
            }
        }

        return view('auth.mfa-challenge', [
            'qrCodeUrl' => $qrCodeUrl,
            'mfaMethod' => strtoupper($mfaMethod),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();

        // 🚨 If user has failed 10 OTP attempts, lock them out instantly
        if ($user->failed_otp_attempts >= 10) {
            return redirect()->route('mfa-challenge.index')->with(['otp_failed_limit' => true]);
        }


        // ✅ Validate OTP
        if ($this->validateOtp($user, $request->code)) {
            session(['two_factor_authenticated' => true]);

            // ✅ Hapus pending_user_id dari sesi (jika ada)
            session()->forget('pending_user_id');

            // ✅ Reset failed attempts and clear OTP data
            $user->mfa->update([
                'two_factor_code' => null,
                'otp_expires_at' => null,
            ]);
            $user->failed_otp_attempts = 0;
            $user->save();

            // ✅ Redirect based on redirect param or fallback to dashboard
            $redirectTo = $request->input('redirect') ?? route($this->getUserDashboard($user));
            return redirect($redirectTo);
        }

        // ❌ OTP is incorrect, increase failed attempts
        $user->increment('failed_otp_attempts');

        $user->save();

        // 🚨 If failed 10 times, trigger lockout
        if ($user->failed_otp_attempts == 10) {
            LoggingService::logSecurityViolation("User [ID: {$user->id}] locked out after 10 OTP failures (MFA method: {$user->mfa->mfa_method})");
            Mail::to('mfa.mypetra@petra.ac.id')->send(new ViolationMail($user, 'otp'));
            return redirect()->route('mfa-challenge.index')->with([
                'otp_failed_limit' => true
            ]);
        }

        return redirect()->route('mfa-challenge.index')->withErrors([
            'code' => "Incorrect OTP. Please try again."
        ]);
    }

    private function handleEmailOtp($user)
    {
        $now = now();

        // Prevent generating a new OTP if still valid
        if ($user->mfa->two_factor_code && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {
            return;
        }

        // Generate new OTP
        $code = $this->generateOtpWithNumber(); // 🔁 ganti jadi yang wajib angka
        $user->mfa->two_factor_code = Crypt::encryptString($code);
        $user->mfa->otp_expires_at = $now->addMinutes(10);
        $user->failed_otp_attempts = 0; // Reset failed attempts on new OTP generation
        $user->mfa->save();




        try {
            Mail::to($user->email)->send(new OtpMail($code));
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("OTP email failed to send to {$user->email}");
            return back()->withErrors(['email' => 'Failed to send email. Please try again.']);
        }
    }



    private function generateGoogleQrCode($user)
    {
        $google2fa = new Google2FA();

        if (!$user->mfa->google2fa_secret) {
            $secretKey = $google2fa->generateSecretKey();
            $user->mfa->google2fa_secret = $secretKey;
            $user->mfa->save();
        }

        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->mfa->google2fa_secret
        );
    }

    public function resendEmailOtp()
    {
        $user = auth()->user();

        // Preserve failed OTP attempts before resetting OTP
        $failedAttempts = $user->failed_otp_attempts;

        // **Reset OTP but keep failed attempts**
        $user->mfa->two_factor_code = null;
        $user->mfa->otp_expires_at = null;
        $user->mfa->save(); // ✅ Ensure the database is updated

        // ✅ Generate and send new OTP
        if ($user->mfa->mfa_method === 'email') {
            $this->handleEmailOtp($user);
        } elseif ($user->mfa->mfa_method === 'whatsapp') {
            $this->handleWhatsAppOtp($user);
        } elseif ($user->mfa->mfa_method === 'sms') {
            $this->handleSmsOtp($user);
        }



        // ✅ Restore failed OTP attempts after regenerating OTP
        $user->failed_otp_attempts = $failedAttempts;
        $user->save(); // ✅ Force save to database

        session()->flash('success', 'A new OTP has been sent.');
        return back();
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

    private function getUserDashboard($user)
    {
        switch ($user->usertype) {
            case 'student':
                return 'student.dashboard';
            case 'admin':
                return 'admin.dashboard';
            case 'staff':
                return 'staff.dashboard';
            default:
                return 'dashboard'; // Public or default dashboard
        }
    }

    public function cancel()
    {
        $user = auth()->user();

        if ($user) {
            // ✅ Ensure the failed attempts reset before logout
            $user->failed_otp_attempts = 0;
            $user->save(); // ✅ Force save to database
        }

        auth()->logout(); // ✅ Log out the user
        session()->invalidate(); // ✅ Clear session completely
        session()->regenerateToken(); // ✅ Prevent CSRF issues

        return redirect()->route('login')->with('info', 'MFA verification canceled.');
    }


    private function handleWhatsAppOtp($user)
    {
        $now = now();

        // Jika OTP masih berlaku, jangan generate ulang
        if ($user->mfa->two_factor_code && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {
            return;
        }

        // Generate OTP baru
        $code = $this->generateOtpWithNumber(); // 🔁 ganti jadi yang wajib angka
        $user->mfa->two_factor_code = Crypt::encryptString($code);
        $user->mfa->otp_expires_at = $now->addMinutes(5);
        $user->mfa->save();
        LoggingService::logMfaEvent("WhatsApp OTP sent to {$user->phone_number}", [
            'method' => 'whatsapp',
        ]);


        // Kirim OTP ke WhatsApp
        $whatsappService = new WhatsAppService();
        $whatsappService->sendOTP($user->phone_number, $code);
    }

    private function handleSmsOtp($user)
    {
        $now = now();

        // Log execution


        // If OTP is still valid, do not regenerate
        if ($user->mfa->two_factor_code && $user->mfa->otp_expires_at && $now->lt($user->mfa->otp_expires_at)) {

            return;
        }

        // Generate new OTP
        $code = $this->generateOtpWithNumber(); // 🔁 ganti jadi yang wajib angka
        $user->mfa->two_factor_code = Crypt::encryptString($code);
        $user->mfa->otp_expires_at = $now->addMinutes(5);
        $user->mfa->save();
        LoggingService::logMfaEvent("Generated OTP for {$user->phone_number}", [
            'method' => 'sms',
            'code' => $code // Optional — you can remove if not safe
        ]);

        // Send OTP via SMS using the correct route
        $smsService = new SmsService();
        $response = $smsService->sendSms($user->phone_number, "Your OTP Code is: $code", 'OTP');



    }

    private function hasReachedMaxDevices($user)
    {
        return TrustedDevice::where('user_id', $user->id)->count() >= 4;
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

    public function showChallenge()
    {
        $user = auth()->user();
        $mfaMethod = optional($user->mfa)->mfa_method; // Ambil metode MFA dari relasi

        return view('auth.mfa-challenge', [
            'mfaMethod' => strtoupper($mfaMethod), // Misal: "SMS", "EMAIL", "GOOGLE_AUTH"
        ]);
    }

}
