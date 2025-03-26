<?php

namespace App\Http\Controllers;

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




class TwoFactorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        LoggingService::logMfaEvent("MFA challenge triggered", [
            'method' => $user->mfa_method,
        ]);
        $qrCodeUrl = null;

        if ($user->mfa_method === 'email') {
            if (!$user->two_factor_code) {
                $this->handleEmailOtp($user);
            }
        } elseif ($user->mfa_method === 'google_auth') {
            $qrCodeUrl = $this->generateGoogleQrCode($user);
        } elseif ($user->mfa_method === 'whatsapp') {
            if (!$user->two_factor_code) {
                $this->handleWhatsAppOtp($user);
            }
        } elseif ($user->mfa_method === 'sms') {
            if (!$user->two_factor_code) {
                $this->handleSmsOtp($user);
            }
        }

        return view('auth.mfa-challenge', [
            'qrCodeUrl' => $qrCodeUrl,
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
            LoggingService::logMfaEvent("User [ID: {$user->id}] entered correct OTP", [
                'method' => $user->mfa_method,
            ]);
            session(['two_factor_authenticated' => true]);

            // ✅ Hapus pending_user_id dari sesi (jika ada)
            session()->forget('pending_user_id');

            // ✅ Reset failed attempts and clear OTP data
            $user->update([
                'two_factor_code' => null,
                'otp_expires_at' => null,
                'failed_otp_attempts' => 0,
            ]);

            // ✅ Redirect based on redirect param or fallback to dashboard
            $redirectTo = $request->input('redirect') ?? route($this->getUserDashboard($user));
            return redirect($redirectTo);
        }

        // ❌ OTP is incorrect, increase failed attempts
        $user->increment('failed_otp_attempts');
        LoggingService::logSecurityViolation("Incorrect OTP for User ID {$user->id}. Attempt {$user->failed_otp_attempts}/10", [
            'method' => $user->mfa_method,
        ]);

        $user->save();

        // 🚨 If failed 10 times, trigger lockout
        if ($user->failed_otp_attempts == 10) {
            LoggingService::logSecurityViolation("User [ID: {$user->id}] locked out after 10 OTP failures (MFA method: {$user->mfa_method})");
            Mail::to('mfa.mypetra@petra.ac.id')->send(new ViolationMail($user, 'otp'));
            return redirect()->route('mfa-challenge.index')->with([
                'otp_failed_limit' => true
            ]);
        }

        return redirect()->route('mfa-challenge.index')->withErrors([
            'code' => "Incorrect OTP. You have " . (10 - $user->failed_otp_attempts) . " attempts left."
        ]);
    }

    private function handleEmailOtp($user)
    {
        $now = now();

        // Prevent generating a new OTP if still valid
        if ($user->two_factor_code && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {
            return;
        }

        // Generate new OTP
        $code = rand(100000, 999999);
        $user->two_factor_code = Crypt::encryptString($code);
        $user->otp_expires_at = $now->addMinutes(10);
        $user->failed_otp_attempts = 0; // Reset failed attempts on new OTP generation
        $user->save();
        LoggingService::logMfaEvent("Generated OTP for {$user->email} (via email)", [
            'method' => 'email',
        ]);



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

        if (!$user->google2fa_secret) {
            $secretKey = $google2fa->generateSecretKey();
            $user->google2fa_secret = $secretKey;
            $user->save();
        }

        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );
    }

    public function resendEmailOtp()
    {
        $user = auth()->user();

        // Preserve failed OTP attempts before resetting OTP
        $failedAttempts = $user->failed_otp_attempts;

        // **Reset OTP but keep failed attempts**
        $user->two_factor_code = null;
        $user->otp_expires_at = null;
        $user->save(); // ✅ Ensure the database is updated

        // ✅ Generate and send new OTP
        if ($user->mfa_method === 'email') {
            $this->handleEmailOtp($user);
        } elseif ($user->mfa_method === 'whatsapp') {
            $this->handleWhatsAppOtp($user);
        } elseif ($user->mfa_method === 'sms') {
            $this->handleSmsOtp($user);
        }
        LoggingService::logMfaEvent("Resend OTP triggered for User ID: {$user->id}", [
            'method' => $user->mfa_method
        ]);


        // ✅ Restore failed OTP attempts after regenerating OTP
        $user->failed_otp_attempts = $failedAttempts;
        $user->save(); // ✅ Force save to database

        session()->flash('success', 'A new OTP has been sent.');
        return back();
    }

    private function validateOtp($user, $code)
    {
        $now = now();

        if ($user->mfa_method === 'email') {
            try {
                $decryptedOtp = Crypt::decryptString($user->two_factor_code);

                // Periksa apakah OTP cocok dan belum kedaluwarsa
                if ($code == $decryptedOtp && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false; // Jika gagal dekripsi, anggap OTP tidak valid
            }
        }

        if ($user->mfa_method === 'google_auth') {
            $google2fa = new Google2FA();
            return $google2fa->verifyKey($user->google2fa_secret, $code);
        }
        if ($user->mfa_method === 'whatsapp') {
            try {
                $decryptedOtp = Crypt::decryptString($user->two_factor_code);

                if ($code == $decryptedOtp && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        if ($user->mfa_method === 'sms') {
            try {
                $decryptedOtp = Crypt::decryptString($user->two_factor_code);

                if ($code == $decryptedOtp && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {
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
        LoggingService::logMfaEvent("User [ID: {$user->id}] canceled MFA verification");
        auth()->logout(); // ✅ Log out the user
        session()->invalidate(); // ✅ Clear session completely
        session()->regenerateToken(); // ✅ Prevent CSRF issues

        return redirect()->route('login')->with('info', 'MFA verification canceled.');
    }


    private function handleWhatsAppOtp($user)
    {
        $now = now();

        // Jika OTP masih berlaku, jangan generate ulang
        if ($user->two_factor_code && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {
            return;
        }

        // Generate OTP baru
        $code = rand(100000, 999999);
        $user->two_factor_code = Crypt::encryptString($code);
        $user->otp_expires_at = $now->addMinutes(5);
        $user->save();
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
        if ($user->two_factor_code && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {

            return;
        }

        // Generate new OTP
        $code = rand(100000, 999999);
        $user->two_factor_code = Crypt::encryptString($code);
        $user->otp_expires_at = $now->addMinutes(5);
        $user->save();
        LoggingService::logMfaEvent("Generated OTP for {$user->phone_number}", [
            'method' => 'sms',
            'code' => $code // Optional — you can remove if not safe
        ]);

        // Send OTP via SMS using the correct route
        $smsService = new SmsService();
        $response = $smsService->sendSms($user->phone_number, "Your OTP Code is: $code", 'OTP');
        LoggingService::logMfaEvent("Zuwinda SMS API Response for {$user->phone_number}", [
            'response' => $response
        ]);


    }

    private function hasReachedMaxDevices($user)
    {
        return TrustedDevice::where('user_id', $user->id)->count() >= 4;
    }




}
