<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpLdapMail;

class OtpLdapVerificationController extends Controller
{
    public function form()
    {
        $otp = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

        session([
            'ldap_otp_code' => $otp,
            'ldap_otp_verified' => false,
        ]);

        // Kirim OTP ke email user
        Mail::to(auth()->user()->email)->send(new OtpLdapMail(
            otpCode: $otp,
            name: auth()->user()->name,
            email: auth()->user()->email,
            time: now()->format('d F Y H:i') . ' WIB',
            path: null
        ));

        return view('admin.ldap.mfa-challenge-ldap', [
            'mfaMethod' => 'email',
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // max 10MB
        ]);

        if ($request->code !== session('ldap_otp_code')) {
            return back()->withErrors(['code' => 'Invalid OTP.']);
        }

        // Simpan file ke public/support_images
        $filename = time() . '_' . $request->file('attachment')->getClientOriginalName();
        $request->file('attachment')->move(public_path('support_images'), $filename);
        $relativePath = 'support_images/' . $filename;

        // Kirim notifikasi ke admin
        Mail::to('mfa.mypetra@petra.ac.id')->send(new OtpLdapMail(
            otpCode: null, // Tidak kirim OTP lagi ke admin
            name: auth()->user()->name,
            email: auth()->user()->email,
            time: now()->format('d F Y H:i') . ' WIB',
            path: $relativePath
        ));

        session(['ldap_otp_verified' => true]);

        return redirect()->route('ldap.index');
    }

    public function resend()
    {
        $otp = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

        session(['ldap_otp_code' => $otp]);

        Mail::to(auth()->user()->email)->send(new OtpLdapMail(
            otpCode: $otp,
            name: auth()->user()->name,
            email: auth()->user()->email,
            time: now()->format('d F Y H:i') . ' WIB',
            path: null
        ));

        return response()->json(['status' => 'resent']);
    }

    public function cancel()
    {
        session()->forget(['ldap_otp_code', 'ldap_otp_verified']);

        return redirect()->route('admin.dashboard');
    }
}
