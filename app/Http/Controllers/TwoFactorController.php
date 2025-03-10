<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use App\Mail\OtpMail;
use App\Models\TrustedDevice;
use Illuminate\Support\Facades\Crypt;


class TwoFactorController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $qrCodeUrl = null;

        if ($user->mfa_method === 'email') {
            if (!$user->two_factor_code) {
                $this->handleEmailOtp($user);
            }
        } elseif ($user->mfa_method === 'google_auth') {
            $qrCodeUrl = $this->generateGoogleQrCode($user);
        } elseif ($user->mfa_method === 'sms') {
            // Placeholder for future SMS implementation
            session()->flash('message', 'SMS authentication is coming soon.');
        }

        return view('auth.mfa-challenge', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }


    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|integer',
        ]);

        $user = auth()->user();

        if ($this->validateOtp($user, $request->code)) {
            session(['two_factor_authenticated' => true]);
            $user->two_factor_code = null;
            $user->otp_expires_at = null; // Hapus waktu kadaluarsa juga
            $user->save();


            // Check if user wants to trust this device
            if ($request->has('trust_device') && $request->trust_device == 'yes') {
                $existingTrusted = TrustedDevice::where('user_id', $user->id)->first();
                if ($existingTrusted) {
                    $existingTrusted->delete(); // Remove previous trusted device
                }

                TrustedDevice::create([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'trusted' => true,
                ]);
            }

            return redirect()->route($this->getUserDashboard($user));

        }

        return redirect()->route('mfa-challenge.index')->withErrors(['code' => 'The provided code is incorrect.']);
    }


    private function handleEmailOtp($user)
    {
        $now = now();

        // Jika OTP masih berlaku, jangan generate ulang
        if ($user->two_factor_code && $user->otp_expires_at && $now->lt($user->otp_expires_at)) {
            return;
        }

        // Generate OTP baru
        $code = rand(100000, 999999);
        $user->two_factor_code = Crypt::encryptString($code); // Enkripsi sebelum disimpan
        $user->otp_expires_at = $now->addMinutes(10); // OTP berlaku 10 menit
        $user->save();

        try {
            Mail::to($user->email)->send(new OtpMail($code)); // Kirim OTP asli ke email
        } catch (\Exception $e) {
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

        // Hapus OTP lama agar bisa digenerate ulang
        $user->two_factor_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Kirim OTP baru
        $this->handleEmailOtp($user);

        return back()->with('success', 'A new OTP has been sent to your email.');
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
        auth()->logout(); // Logout pengguna
        session()->forget('two_factor_authenticated'); // Hapus sesi MFA
        return redirect()->route('login')->with('info', 'MFA verification canceled.');
    }




}
