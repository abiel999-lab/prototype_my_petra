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

        $fullEmail = $request->uid . '@' . $request->domain;

        // Cek duplikasi di database
        if (User::where('email', $fullEmail)->exists()) {
            return back()->withErrors(['uid' => 'Akun dengan UID dan domain ini sudah terdaftar.']);
        }

        $ldapUser = null;
        $usertype = null;

        // 1. Coba koneksi student
        try {
            $ldapUser = Container::getConnection('student')
                ->query()
                ->where('uid', '=', $request->uid)
                ->first();

            if ($ldapUser) {
                $usertype = 'student';
            }
        } catch (\Exception $e) {
            // log or ignore
        }

        // 2. Jika tidak ditemukan di student, coba koneksi default (staff)
        if (!$ldapUser) {
            try {
                $ldapUser = Container::getConnection('default')
                    ->query()
                    ->where('uid', '=', $request->uid)
                    ->first();

                if ($ldapUser) {
                    $usertype = 'staff';
                }
            } catch (\Exception $e) {
                return back()->withErrors(['uid' => 'Gagal menghubungi server LDAP.']);
            }
        }

        if (!$ldapUser) {
            return back()->withErrors(['uid' => 'UID tidak ditemukan dalam sistem LDAP manapun.']);
        }

        $password = $this->generatePasswordFromUid($request->uid);

        $user = User::create([
            'name' => $request->uid,
            'email' => $fullEmail,
            'password' => Hash::make($password),
            'usertype' => $usertype,
            'email_verified_at' => now(),
        ]);

        Mail::to($request->email_confirmation)->send(
            new LdapAccountCreatedMail($request->uid, $fullEmail, $password)
        );

        return back()->with('status', 'Akun berhasil dibuat. Silakan cek email Anda untuk detail login.');
    }

}
