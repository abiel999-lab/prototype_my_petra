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
        $prefix = strtoupper($uid);
        $random = substr(bin2hex(random_bytes(3)), 0, 6);
        return $prefix . '!' . $random;
    }

    public function register(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'domain' => 'required|in:john.petra.ac.id,peter.petra.ac.id,petra.ac.id',
            'email_confirmation' => 'required|email',
        ]);

        $uid = $request->uid;
        $domain = $request->domain;
        $fullEmail = $uid . '@' . $domain;

        if (User::where('email', $fullEmail)->exists()) {
            return back()->withErrors(['uid' => 'Akun dengan UID dan domain ini sudah terdaftar.']);
        }

        // 🧠 Role ditentukan dari domain
        $usertype = match ($domain) {
            'john.petra.ac.id' => 'student',
            'peter.petra.ac.id', 'petra.ac.id' => 'staff',
        };

        // 🔍 Coba cari UID di LDAP student
        try {
            $ldapUser = Container::getConnection('student')->query()->where('uid', '=', $uid)->first();
        } catch (\Exception $e) {
            $ldapUser = null;
        }

        // 🔍 Kalau belum ketemu, coba cari di LDAP default
        if (!$ldapUser) {
            try {
                $ldapUser = Container::getConnection('default')->query()->where('uid', '=', $uid)->first();
            } catch (\Exception $e) {
                return back()->withErrors(['uid' => 'Gagal menghubungi server LDAP.']);
            }
        }

        // ❌ Tetap tidak ketemu
        if (!$ldapUser) {
            return back()->withErrors(['uid' => 'UID tidak ditemukan dalam server LDAP.']);
        }

        // ✅ Simpan user lokal
        $password = $this->generatePasswordFromUid($uid);

        $user = User::create([
            'name' => $ldapUser['cn'][0] ?? $uid,
            'email' => $fullEmail,
            'password' => Hash::make($password),
            'usertype' => $usertype,
            'email_verified_at' => now(),
        ]);

        Mail::to($request->email_confirmation)->send(
            new LdapAccountCreatedMail($uid, $fullEmail, $password)
        );

        return back()->with('status', 'Akun berhasil dibuat. Silakan cek email Anda untuk detail login.');
    }
}
