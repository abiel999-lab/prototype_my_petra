<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\LoggingService;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // ✅ Determine usertype based on email domain
        $email = $request->email;
        $usertype = 'general';

        if (str_ends_with($email, '@john.petra.ac.id')) {
            $usertype = 'student';
        } elseif (str_ends_with($email, '@peter.petra.ac.id')) {
            $usertype = 'staff';
        } elseif (str_ends_with($email, '@petra.ac.id')) {
            $usertype = 'staff';
        }

        // ✅ Create user with role
        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'usertype' => $usertype,
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::random(60),
        ]);

        event(new Registered($user));

        LoggingService::logMfaEvent("New user registered", [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 🔐 Sinkronisasi ke Active Directory (LDAP)
        try {
            $uid = explode('@', $email)[0];

            $ldap = new LdapUser;
            $ldap->cn = $request->name;
            $ldap->sAMAccountName = $uid;
            $ldap->userPrincipalName = $email;
            $ldap->mail = $email;

            $quotedPwd = iconv('UTF-8', 'UTF-16LE', '"' . $request->password . '"');
            $ldap->unicodePwd = $quotedPwd;

            $ldap->setDn("cn={$request->name},ou=staff,dc=petra,dc=ac,dc=id"); // OU bisa disesuaikan
            $ldap->save();

            LoggingService::logMfaEvent("User {$email} registered and synced to LDAP", []);
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("LDAP sync failed for new registered user {$email}: " . $e->getMessage(), []);
        }

        Auth::login($user);
        // ✅ Redirect based on usertype
        if ($user->usertype === 'student') {
            return redirect()->route('student.dashboard');
        } elseif ($user->usertype === 'staff') {
            return redirect()->route('staff.dashboard');
        }


        return redirect(route('dashboard', absolute: false));
    }

}
