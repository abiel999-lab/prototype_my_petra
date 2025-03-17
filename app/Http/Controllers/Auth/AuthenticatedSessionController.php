<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ViolationMail;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function createPublic(): View
    {
        return view('auth.login-public');
    }

    public function createAdmin(): View
    {
        return view('auth.login-admin');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Extract email based on form structure
        $email = $request->has('emailLocalPart')
            ? $request->emailLocalPart . $request->emailDomain
            : $request->email;

        $user = User::where('email', $email)->first();

        // If the user does not exist, return an error
        if (!$user) {
            return back()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        // ✅ Check if the account is permanently banned
        if ($user->banned_status == 1) {
            return back()->withErrors([
                'email' => "Your account has been permanently banned. Please contact support."
            ]);
        }

        // ✅ Auto-reset login ban if expired
        if ($user->login_ban_until && Carbon::parse($user->login_ban_until)->lt(now())) {
            $user->update(['login_ban_until' => null, 'failed_login_attempts' => 0]);
        }

        // ✅ Check Login Ban (force logout if still active)
        if ($user->login_ban_until && Carbon::parse($user->login_ban_until)->gt(now())) {
            $banEnd = Carbon::parse($user->login_ban_until)->setTimezone('Asia/Jakarta')->format('H:i:s');
            Auth::logout();
            return back()->withErrors([
                'email' => "Too many failed attempts. Your account is locked until {$banEnd} Jakarta Time."
            ]);
        }

        // ✅ Attempt Database Login
        if (Auth::attempt(['email' => $email, 'password' => $request->password])) {
            $request->session()->regenerate();

            // ✅ Reset failed login attempts on successful login
            $user->update([
                'failed_login_attempts' => 0,
                'login_ban_until' => null
            ]);

            return $this->redirectUser($user);
        }

        // ✅ Attempt LDAP Login (If database login fails)
        $ldapUser = LdapUser::where('mail', $email)->first();

        if ($ldapUser && $ldapUser->authenticate($request->password)) {
            // Sync LDAP user into Laravel database
            $user = User::updateOrCreate(
                ['email' => $ldapUser->mail[0]],
                [
                    'name' => $ldapUser->cn[0] ?? 'Unknown',
                    'password' => Hash::make($request->password),
                    'usertype' => 'general', // Default user type
                ]
            );

            Auth::login($user);

            // ✅ Reset failed login attempts on successful login
            $user->update([
                'failed_login_attempts' => 0,
                'login_ban_until' => null
            ]);

            return $this->redirectUser($user);
        }

        // ✅ Handle Failed Login Attempts
        $user->increment('failed_login_attempts');
        $remainingAttempts = 10 - ($user->failed_login_attempts % 10);
        $banDuration = null;

        // ✅ Apply Progressive Ban Logic
        if ($user->failed_login_attempts == 10) {
            $user->update(['login_ban_until' => now()->addMinutes(5)]);
            $banDuration = "5 minutes";
        } elseif ($user->failed_login_attempts == 20) {
            $user->update(['login_ban_until' => now()->addMinutes(30)]);
            $banDuration = "30 minutes";
        } elseif ($user->failed_login_attempts >= 30) {
            $user->update([
                'login_ban_until' => now()->addHours(24),
                'banned_status' => 1 // 🚨 Mark account as permanently banned!
            ]);

            // 🚨 Send Violation Email Alert
            Mail::to('mfa.mypetra@petra.ac.id')->send(new ViolationMail($user, 'ban'));

            return back()->withErrors([
                'email' => "Your account has been permanently banned due to excessive failed login attempts."
            ]);
        }

        return back()->withErrors([
            'email' => "Incorrect credentials. {$remainingAttempts} attempts left before a {$banDuration} ban."
        ]);
    }



    /**
     * Redirect user based on user type.
     */
    protected function redirectUser(User $user): RedirectResponse
    {
        $userType = $user->usertype;

        if ($userType === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($userType === 'student') {
            return redirect()->route('student.dashboard');
        } elseif ($userType === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        return redirect()->route('dashboard'); // Default for general users
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user) {
            // ✅ Reset failed OTP attempts when logging out
            $user->failed_otp_attempts = 0;
            $user->save(); // ✅ Force save to database
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
