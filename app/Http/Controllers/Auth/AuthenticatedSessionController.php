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
use App\Http\Controllers\UserManagement\UserDeviceController;
use App\Services\LoggingService;
use Illuminate\Support\Str;
use App\Mail\MagicLinkMail;



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
        $email = $request->has('emailLocalPart')
            ? $request->emailLocalPart . $request->emailDomain
            : $request->email;

        $user = User::where('email', $email)->first();

        // ✅ If user exists in database, process login
        if ($user) {
            // 🚨 Check if the account is permanently banned
            if ($user->banned_status == 1) {
                return back()->withErrors([
                    'email' => "Your account has been permanently banned. Please contact support."
                ]);
            }

            // ✅ Auto-reset login ban if expired
            if ($user->login_ban_until && Carbon::parse($user->login_ban_until)->lt(now())) {
                $user->update(['login_ban_until' => null, 'failed_login_attempts' => 0]);
            }

            // ✅ Check login ban
            if ($user->login_ban_until && Carbon::parse($user->login_ban_until)->gt(now())) {
                $banEnd = Carbon::parse($user->login_ban_until)->setTimezone('Asia/Jakarta')->format('H:i:s');
                Auth::logout();
                return back()->withErrors([
                    'email' => "Too many failed attempts. Your account is locked."
                ]);
            }

            // ✅ Attempt Login
            if (Auth::attempt(['email' => $email, 'password' => $request->password])) {
                $request->session()->regenerate();
                LoggingService::logMfaEvent("User [ID: {$user->id}] logged in via local email", [
                    'login_method' => 'email',
                ]);

                // ✅ Check OS limit before proceeding
                $userId = Auth::id();
                $deviceController = new UserDeviceController();
                $deviceLimitCheck = $deviceController->handleDeviceTracking($userId);

                if ($deviceLimitCheck instanceof RedirectResponse) {
                    return $deviceLimitCheck; // 🚨 Redirect to warning page if OS limit reached
                }

                // ✅ Reset failed login attempts on successful login
                $user->failed_login_attempts = 0;
                $user->login_ban_until = null;
                $user->save(); // ✅ Force save to database

                return $this->redirectUser($user);
            }

            // ❌ Incorrect password, increase failed attempts
            $user->increment('failed_login_attempts');

            $user->save(); // ✅ Ensure the new failed attempt is saved

            $remainingAttempts = 15 - $user->failed_login_attempts;

            // 🚨 Ban the account if failed attempts exceed 15
            if ($user->failed_login_attempts >= 15) {
                LoggingService::logSecurityViolation("User [ID: {$user->id}] permanently banned after 15 failed login attempts");
                $user->banned_status = true;
                $user->save(); // ✅ Ensure the banned status is updated in DB

                // 🚨 Send Violation Email Alert
                Mail::to('mfa.mypetra@petra.ac.id')->send(new ViolationMail($user, 'login'));

                return back()->withErrors([
                    'email' => "Your account has been permanently banned due to excessive failed login attempts."
                ]);
            }

            return back()->withErrors([
                'email' => "Incorrect credentials."
            ]);
        }

        // If user is not found in database, check LDAP
        try {
            $ldapUser = LdapUser::where('mail', $email)->first();

            if ($ldapUser) {
                $user = User::where('email', $ldapUser->mail[0])->first();
                $firstLogin = !$user;

                if ($firstLogin) {
                    $email = $ldapUser->mail[0];
                    $usertype = 'general';

                    if (str_ends_with($email, '@john.petra.ac.id')) {
                        $usertype = 'student';
                    } elseif (str_ends_with($email, '@peter.petra.ac.id')) {
                        $usertype = 'staff';
                    } elseif (str_ends_with($email, '@petra.ac.id')) {
                        $usertype = 'staff';
                    }

                    $user = User::create([
                        'email' => $email,
                        'name' => $ldapUser->cn[0] ?? 'Unknown',
                        'password' => Hash::make($request->password),
                        'usertype' => $usertype,
                    ]);
                }

                Auth::login($user);
                LoggingService::logMfaEvent("LDAP login successful for {$email} (User ID: {$user->id})", [
                    'login_method' => 'ldap',
                ]);

                // ✅ Check OS limit before proceeding
                $userId = Auth::id();
                $deviceController = new UserDeviceController();
                $deviceLimitCheck = $deviceController->handleDeviceTracking($userId);

                if ($deviceLimitCheck instanceof RedirectResponse) {
                    return $deviceLimitCheck; // 🚨 Redirect to warning page if OS limit reached
                }

                // ✅ Reset failed login attempts for newly created LDAP user
                $user->failed_login_attempts = 0;
                $user->login_ban_until = null;
                $user->save(); // ✅ Force save to database

                return $this->redirectUser($user);
            }
        } catch (\Exception $e) {
            LoggingService::logSecurityViolation("LDAP login failed for email {$email}. Reason: " . $e->getMessage());
            return back()->withErrors([
                'email' => "Email or Password Not Found."
            ]);
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.']);
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
        if ($user && $user->usertype === 'admin') {
            $user->temporary_role = null;
            $user->save();
        }

        // 🚫 Prevent logout if MFA is enabled and phone is required but missing (just in case)
        if (
            $user->mfa_enabled &&
            in_array($user->mfa_method, ['whatsapp', 'sms']) &&
            empty($user->phone_number)
        ) {
            return redirect()->back()->withErrors([
                'logout' => 'Logout blocked: You must add your phone number before logging out when using WhatsApp or SMS MFA.'
            ]);
        }

        LoggingService::logMfaEvent("User [ID: {$user->id}] logged out successfully");

        // ✅ Reset OTP attempt on logout
        $user->failed_otp_attempts = 0;
        $user->save();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showPasswordlessForm(): View
    {
        return view('auth.passwordless-request');
    }

    public function sendMagicLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->passwordless_enabled) {
            return back()->withErrors(['email' => 'Passwordless login not allowed for this account.']);
        }

        $token = Str::random(64);
        $user->passwordless_token = hash('sha256', $token);
        $user->passwordless_expires_at = now()->addMinutes(15);
        $user->save();

        $link = route('passwordless.verify', ['token' => $token]);
        Mail::to($user)->send(new MagicLinkMail($link));

        return back()->with('status', 'login link sent to your email.');
    }

    public function verifyMagicLink($token): RedirectResponse
    {
        $hashed = hash('sha256', $token);
        $user = User::where('passwordless_token', $hashed)
            ->where('passwordless_expires_at', '>', now())
            ->first();

        if (!$user) {
            abort(403, 'Invalid or expired token.');
        }

        // 🚫 Tambahkan pengecekan banned
        if ($user->banned_status == 1) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is permanently banned. Passwordless login denied.'
            ]);
        }

        Auth::login($user);
        $user->update([
            'passwordless_token' => null,
            'passwordless_expires_at' => null
        ]);

        LoggingService::logMfaEvent("User [ID: {$user->id}] logged in via passwordless login link", [
            'login_method' => 'magic_link',
        ]);

        return $user->mfa_enabled
            ? redirect()->route('mfa-challenge.index') // Sesuai metode MFA yang digunakan
            : $this->redirectUser($user);
    }




}
