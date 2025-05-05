<?php

use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Profile\TwoFactorController;
use App\Http\Controllers\UserManagement\UserController;
use App\Http\Controllers\Profile\SessionController;
use App\Http\Middleware\Session\StoreUserSession;
use App\Http\Controllers\UserManagement\UserDeviceController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Support\SupportController;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\TrustedDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Profile\ExternalMfaController;
use App\Services\LoggingService;
use App\Http\Controllers\Dashboard\LogViewerController;
use App\Http\Controllers\UserManagement\RoleSwitchController;

// ðŸ”¹ Redirect root URL ('/') to the correct dashboard or login
Route::middleware(['ip.limiter'])->get('/', function () {
    if (Auth::check()) {
        switch (Auth::user()->usertype) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            case 'staff':
                return redirect()->route('staff.dashboard');
            case 'general':
                return redirect()->route('dashboard');
            default:
                return redirect()->route('dashboard'); // Default for unknown user types
        }
    }
    return redirect()->route('login'); // Redirect to login if not logged in
})->name('home');

// ðŸ”¹ Google OAuth Routes
Route::get('auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('google.login');

Route::get('auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->stateless()->user(); // Use stateless() to avoid session issues

        // Check if user exists
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // 🚨 Prevent banned users from logging in
            if ($user->banned_status) {
                return redirect()->route('login')->withErrors([
                    'email' => "Your account is banned. Please contact support."
                ]);
            }

            // ✅ Update Google ID and Reset Failed Login Attempts
            $user->update([
                'google_id' => $googleUser->getId(),

            ]);
        } else {
            // Create new user with 'general' as default usertype
            $email = $googleUser->getEmail();
            $usertype = 'general';

            if (str_ends_with($email, '@john.petra.ac.id')) {
                $usertype = 'student';
            } elseif (str_ends_with($email, '@peter.petra.ac.id')) {
                $usertype = 'staff';
            } elseif (str_ends_with($email, '@petra.ac.id')) {
                $usertype = 'staff';
            }

            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(uniqid()),
                'usertype' => $usertype,
                'banned_status' => false,
                'failed_login_attempts' => 0,
            ]);
            // if (!$user->mfa()->exists()) {
            //     $user->mfa()->create([
            //         'mfa_enabled' => false,
            //         'mfa_method' => 'email',
            //     ]);
            // }


        }

        Auth::login($user);
        LoggingService::logMfaEvent("Google OAuth login successful for {$user->email}", [
            'user_id' => $user->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);




        // ✅ Call OS limit check before redirecting to the dashboard
        $userId = Auth::id();
        $deviceController = new UserDeviceController();
        $deviceLimitCheck = $deviceController->handleDeviceTracking($userId);

        if ($deviceLimitCheck instanceof \Illuminate\Http\RedirectResponse) {
            LoggingService::logSecurityViolation("Device limit hit for user [ID: {$userId}] during Google OAuth login");
            return $deviceLimitCheck; // 🚨 Redirect to warning page if OS limit is reached
        }

        // Redirect based on user type
        switch ($user->usertype) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            case 'staff':
                return redirect()->route('staff.dashboard');
            case 'general':
            default:
                return redirect()->route('dashboard');
        }
    } catch (\Exception $e) {
        LoggingService::logSecurityViolation("Google OAuth error: " . $e->getMessage());
        return redirect()->route('login')->with('error', 'Google authentication failed.');
    }
})->name('google.callback');

// 🔹 LDAP Authentication (Custom Login Handler)
Route::post('/login', function (Request $request) {
    // If the request contains emailLocalPart, construct the email address
    $email = $request->has('emailLocalPart')
        ? $request->emailLocalPart . $request->emailDomain
        : $request->email;

    $credentials = [
        'email' => $email,
        'password' => $request->password,
    ];

    // 🔹 Attempt database login first
    if (Auth::attempt($credentials)) {
        switch (Auth::user()->usertype) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            case 'staff':
                return redirect()->route('staff.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }

    // 🔹 Attempt LDAP authentication if database login fails
    try {
        $ldapUser = LdapUser::where('mail', $credentials['email'])->first();

        if ($ldapUser && $ldapUser->authenticate($credentials['password'])) {
            // Sync LDAP user into Laravel database
            $email = $ldapUser->mail[0];
            $user = User::where('email', $email)->first();
            $firstLogin = !$user;

            if ($firstLogin) {
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
                    'password' => Hash::make($credentials['password']),
                    'usertype' => $usertype,
                ]);
            }


            Auth::login($user);
            LoggingService::logMfaEvent("LDAP login success for {$user->email}", [
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);


            switch ($user->usertype) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'student':
                    return redirect()->route('student.dashboard');
                case 'staff':
                    return redirect()->route('staff.dashboard');
                default:
                    return redirect()->route('dashboard');
            }
        }
    } catch (\Exception $e) {
        LoggingService::logSecurityViolation("LDAP login failed for {$credentials['email']}: " . $e->getMessage());
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
})->name('login');

// ðŸ”¹ Authentication Middleware
Route::middleware('auth')->group(function () {
    Route::get('/mfa-challenge', [TwoFactorController::class, 'index'])->name('mfa-challenge.index');
    Route::post('/mfa-challenge/verify', [TwoFactorController::class, 'verify'])->name('mfa-challenge.verify');
    Route::post('/mfa-challenge/resend', [TwoFactorController::class, 'resendEmailOtp'])->name('mfa-challenge.resend');
    Route::post('/mfa-challenge/cancel', [TwoFactorController::class, 'cancel'])->name('mfa-challenge.cancel');
    Route::post('/toggle-mfa', [ProfileController::class, 'toggleMfa'])->name('toggle-mfa');
    Route::post('/set-mfa-method', [ProfileController::class, 'setMfaMethod'])->name('set-mfa-method');
    Route::post('/mfa-challenge/send-otp', [TwoFactorController::class, 'handleWhatsAppOtp'])->name('mfa-challenge.send-otp');
    Route::get('/mfa-challenge-external', [ExternalMfaController::class, 'handle'])->name('mfa-challenge-external');
    Route::post('/mfa-challenge-external/verify', [ExternalMfaController::class, 'verify'])->name('mfa-challenge-external.verify');
});


// ðŸ”¹ Authenticated Routes (Protected by MFA & Session Middleware)
Route::middleware(['auth', 'mfachallenge', StoreUserSession::class])->group(function () {

    // 🧭 Route impersonasi dashboard untuk admin dan staff
    Route::get('/role-switch', [RoleSwitchController::class, 'showForm'])->name('role.switch');
    Route::post('/role-switch', [RoleSwitchController::class, 'switch'])->name('role.switch.update');
    Route::middleware(['role:student'])->get('/student/dashboard', fn() => view('student.dashboard'))->name('student.dashboard');
    Route::middleware(['role:staff'])->get('/staff/dashboard', fn() => view('staff.dashboard'))->name('staff.dashboard');
    Route::middleware(['role:general'])->get('/dashboard', fn() => view('general.dashboard'))->name('general.dashboard');


    // ðŸ”¹ Admin Routes
    Route::middleware(['role:admin'])->group(function () {


        Route::get('/admin/dashboard', [HomeController::class, 'indexAdmin'])->name('admin.dashboard');
        Route::get('/admin/setting', [ProfileController::class, 'adminprofile'])->name('profile.admin.setting');
        Route::get('/admin/setting/profile', [ProfileController::class, 'admineditprofile'])->name('profile.admin.profile');
        Route::get('/admin/setting/mfa', [ProfileController::class, 'adminmfasetting'])->name('profile.admin.mfa');
        Route::get('/admin/setting/manage-user', [UserController::class, 'index'])->name('profile.admin.manageuser');
        Route::post('/admin/setting/manage-user/store', [UserController::class, 'store'])->name('profile.admin.manageuser.store');
        Route::put('/admin/setting/manage-user/update/{user}', [UserController::class, 'update'])->name('profile.admin.manageuser.update');
        Route::delete('/admin/setting/manage-user/delete/{user}', [UserController::class, 'destroy'])->name('profile.admin.manageuser.delete');
        Route::post('/admin/setting/manageuser/ban/{user}', [UserController::class, 'ban'])->name('profile.admin.manageuser.ban');
        Route::post('/admin/setting/manageuser/unban/{user}', [UserController::class, 'unban'])->name('profile.admin.manageuser.unban');
        Route::get('/admin/setting/session', [SessionController::class, 'Adminshow'])->name('profile.admin.session.show');
        Route::delete('/admin/setting/session/{id}', [SessionController::class, 'Adminrevoke'])->name('profile.admin.session.revoke');
        Route::post('/admin/setting/session/revoke-all', [SessionController::class, 'AdminrevokeAll'])->name('profile.admin.session.revokeAll');
        Route::delete('/admin/setting/manage-user/session/{id}', [SessionController::class, 'AdminRevokeFromManageUser'])->name('profile.admin.manageuser.session.revoke');
        Route::get('/admin/setting/mfa', [UserDeviceController::class, 'Adminindex'])->name('profile.admin.mfa');
        Route::delete('/admin/setting/mfa/{id}', [UserDeviceController::class, 'Admindelete'])->name('profile.admin.mfa.delete');
        Route::post('/admin/setting/mfa/{id}/trust', [UserDeviceController::class, 'Admintrust'])->name('profile.admin.mfa.trust');
        Route::post('/admin/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Adminuntrust'])->name('profile.admin.mfa.untrust');
        Route::get('/admin/external/setting/mfa', [ProfileController::class, 'adminmfasettingexternal'])->name('profile.admin.mfa.external');
        Route::post('/profile/admin/toggle-passwordless', [ProfileController::class, 'adminTogglePasswordless'])->name('profile.admin.toggle-passwordless');
        Route::get('/admin/logs', [LogViewerController::class, 'index'])->name('admin.logs');
    });

    // ðŸ”¹ Student Routes
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/dashboard', [HomeController::class, 'indexStudent'])->name('student.dashboard');
        Route::get('/student/setting', [ProfileController::class, 'studentprofile'])->name('profile.student.setting');
        Route::get('/student/setting/profile', [ProfileController::class, 'studenteditprofile'])->name('profile.student.profile');
        Route::get('/student/setting/mfa', [ProfileController::class, 'studentmfasetting'])->name('profile.student.mfa');

        Route::get('/student/setting/session', [SessionController::class, 'Studentshow'])->name('profile.student.session.show');
        Route::delete('/student/setting/session/{id}', [SessionController::class, 'Studentrevoke'])->name('profile.student.session.revoke');
        Route::post('/student/setting/session/revoke-all', [SessionController::class, 'StudentrevokeAll'])->name('profile.student.session.revokeAll');

        Route::get('/student/setting/mfa', [UserDeviceController::class, 'Studentindex'])->name('profile.student.mfa');
        Route::delete('/student/setting/mfa/{id}', [UserDeviceController::class, 'Studentdelete'])->name('profile.student.mfa.delete');
        Route::post('/student/setting/mfa/{id}/trust', [UserDeviceController::class, 'Studenttrust'])->name('profile.student.mfa.trust');
        Route::post('/student/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Studentuntrust'])->name('profile.student.mfa.untrust');

        // Student External MFA Route
        Route::get('/student/external/setting/mfa', [ProfileController::class, 'studentmfasettingexternal'])->name('profile.student.mfa.external');

        Route::post('/profile/student/toggle-passwordless', [ProfileController::class, 'studentTogglePasswordless'])->name('profile.student.toggle-passwordless');


    });

    // ðŸ”¹ Staff Routes
    Route::middleware(['role:staff'])->group(function () {
        Route::get('/staff/dashboard', [HomeController::class, 'indexStaff'])->name('staff.dashboard');
        Route::get('/staff/setting', [ProfileController::class, 'staffprofile'])->name('profile.staff.setting');
        Route::get('/staff/setting/profile', [ProfileController::class, 'staffeditprofile'])->name('profile.staff.profile');
        Route::get('/staff/setting/mfa', [ProfileController::class, 'staffmfasetting'])->name('profile.staff.mfa');

        Route::get('/staff/setting/session', [SessionController::class, 'Staffshow'])->name('profile.staff.session.show');
        Route::delete('/staff/setting/session/{id}', [SessionController::class, 'Staffrevoke'])->name('profile.staff.session.revoke');
        Route::post('/staff/setting/session/revoke-all', [SessionController::class, 'StaffrevokeAll'])->name('profile.staff.session.revokeAll');

        Route::get('/staff/setting/mfa', [UserDeviceController::class, 'Staffindex'])->name('profile.staff.mfa');
        Route::delete('/staff/setting/mfa/{id}', [UserDeviceController::class, 'Staffdelete'])->name('profile.staff.mfa.delete');
        Route::post('/staff/setting/mfa/{id}/trust', [UserDeviceController::class, 'Stafftrust'])->name('profile.staff.mfa.trust');
        Route::post('/staff/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Staffuntrust'])->name('profile.staff.mfa.untrust');

        // Staff External MFA Route
        Route::get('/staff/external/setting/mfa', [ProfileController::class, 'staffmfasettingexternal'])->name('profile.staff.mfa.external');

        Route::post('/profile/staff/toggle-passwordless', [ProfileController::class, 'staffTogglePasswordless'])->name('profile.staff.toggle-passwordless');
    });

    // ðŸ”¹ General User Routes
    Route::middleware(['role:general'])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        Route::get('/setting', [ProfileController::class, 'profile'])->name('profile.setting');
        Route::get('/setting/profile', [ProfileController::class, 'editprofile'])->name('profile.profile');
        Route::get('/setting/mfa', [ProfileController::class, 'mfasetting'])->name('profile.mfa');

        Route::get('/setting/session', [SessionController::class, 'show'])->name('profile.session.show');
        Route::delete('/setting/session/{id}', [SessionController::class, 'revoke'])->name('profile.session.revoke');
        Route::post('/setting/session/revoke-all', [SessionController::class, 'revokeAll'])->name('profile.session.revokeAll');

        Route::get('/setting/mfa', [UserDeviceController::class, 'Generalindex'])->name('profile.mfa');
        Route::delete('/setting/mfa/{id}', [UserDeviceController::class, 'Generaldelete'])->name('profile.mfa.delete');
        Route::post('/setting/mfa/{id}/trust', [UserDeviceController::class, 'Generaltrust'])->name('profile.mfa.trust');
        Route::post('/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Generaluntrust'])->name('profile.mfa.untrust');

        // General External MFA Route
        Route::get('/external/setting/mfa', [ProfileController::class, 'mfasettingexternal'])->name('profile.mfa.external');

        Route::post('/profile/toggle-passwordless', [ProfileController::class, 'generalTogglePasswordless'])->name('profile.toggle-passwordless');
    });
});


// ðŸ”¹ Authentication Routes (Login & Authentication)
require __DIR__ . '/auth.php';

// ðŸ”¹ Public Login Pages
Route::middleware(['ip.limiter'])->get('/login/public', [AuthenticatedSessionController::class, 'createPublic'])->name('login.public');
Route::middleware(['ip.limiter'])->get('/login/admin', [AuthenticatedSessionController::class, 'createAdmin'])->name('login.admin');

// 🔹 Device Limit Warning Route (Must be Public)
Route::get('/device-limit-warning', function () {
    return view('auth.device-limit-warning');
})->name('device-limit-warning');
Route::post('/send-mfa-link', [UserDeviceController::class, 'sendExternalEmailLink'])->name('send.mfa.link');


// ðŸ”¹ Email & Password Check
Route::post('/check-email-password', [AuthController::class, 'checkEmailAndPassword'])->name('checkEmailAndPassword');
Route::get('/customer-support', [SupportController::class, 'index'])->name('customer-support');
Route::post('/customer-support/send', [SupportController::class, 'sendEmail'])->name('customer-support.send');
Route::put('/profile/update-phone', [ProfileController::class, 'updatePhone'])->name('profile.update.phone');

// Passwordless Login
Route::get('/passwordless/request', [AuthenticatedSessionController::class, 'showPasswordlessForm'])->name('passwordless.request');
Route::post('/passwordless/request', [AuthenticatedSessionController::class, 'sendMagicLink'])->name('passwordless.send');
Route::get('/passwordless/verify/{token}', [AuthenticatedSessionController::class, 'verifyMagicLink'])->name('passwordless.verify');

