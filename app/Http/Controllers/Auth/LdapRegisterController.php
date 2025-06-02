<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LdapAccountCreatedMail;
use LdapRecord\Container;

class LdapRegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register-ldap');
    }

    private function generatePasswordFromUid(string $uid): string
    {
        $prefix = strtoupper($uid); // Kapitalisasi UID
        $random = substr(bin2hex(random_bytes(3)), 0, 6); // 6 karakter hex
        return $prefix . '!' . $random; // Contoh: C14210157!e1a9f4
    }


    public function register(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'domain' => 'required|in:john.petra.ac.id,peter.petra.ac.id,petra.ac.id',
            'email_confirmation' => 'required|email',
        ]);

        $fullEmail = $request->uid . '@' . $request->domain;

        // Cek di database apakah sudah ada
        if (User::where('email', $fullEmail)->exists()) {
            return back()->withErrors(['uid' => 'Akun dengan UID dan domain ini sudah terdaftar.']);
        }

        // Cek keberadaan UID di LDAP
        $ldapUser = Container::getDefaultConnection()
            ->query()
            ->where('uid', '=', $request->uid)
            ->first();

        if (!$ldapUser) {
            return back()->withErrors(['uid' => 'UID tidak ditemukan dalam sistem LDAP.']);
        }

        // Tentukan usertype berdasarkan domain
        $usertype = $request->domain === 'john.petra.ac.id' ? 'student' : 'staff';

        $password = $this->generatePasswordFromUid($request->uid);

        // Buat akun lokal
        $user = User::create([
            'name' => $request->uid,
            'email' => $fullEmail,
            'password' => Hash::make($password),
            'usertype' => $usertype,
            'email_verified_at' => now(),
        ]);

        // Kirim email konfirmasi
        Mail::to($request->email_confirmation)->send(
            new LdapAccountCreatedMail($request->uid, $fullEmail, $password)
        );

        return back()->with('status', 'Akun berhasil dibuat. Silakan cek email Anda untuk detail login.');
    }
}
