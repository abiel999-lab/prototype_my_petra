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
use App\Ldap\StaffUser as StaffLdapUser;
use App\Ldap\StudentUser as StudentLdapUser;
use App\Ldap\LocalUser as LocalLdapUser; // untuk LDAP docker kamu
use LdapRecord\Container;
use App\Models\TrustedDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Profile\ExternalMfaController;
use App\Services\LoggingService;
use App\Http\Controllers\Dashboard\LogViewerController;
use App\Http\Controllers\UserManagement\RoleSwitchController;
use App\Http\Controllers\Auth\LdapRegisterController;
use App\Http\Controllers\Auth\LdapManageController;
use App\Http\Controllers\Auth\OtpLdapVerificationController;
use App\Http\Controllers\Sso\SsoController;
use App\Http\Controllers\Profile\ExtendedMfaController;
use Illuminate\Support\Facades\Session;
use App\Services\LdapGoogleSyncService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LdapLoginController;



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


// 🔹 Google OAuth Routes
Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google.login');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// LDAP Login Route
Route::post('/login', [LdapLoginController::class, 'login'])->name('login');


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
    Route::get('/extended-mfa', [ExtendedMfaController::class, 'showChallenge'])->name('extended-mfa.challenge');
    Route::post('/extended-mfa', [ExtendedMfaController::class, 'verifyChallenge'])->name('extended-mfa.verify');
    Route::post('/extended-mfa/resend', [ExtendedMfaController::class, 'resend'])->name('extended-mfa.resend');
    Route::post('/extended-mfa/cancel', [ExtendedMfaController::class, 'cancel'])->name('extended-mfa.cancel');
    Route::post('/admin/setting/extended-mfa-setting', [ExtendedMfaController::class, 'updateSetting'])->name('profile.extended-mfa.setting');
});


// ðŸ”¹ Authenticated Routes (Protected by MFA & Session Middleware)
Route::middleware(['auth', 'mfachallenge', StoreUserSession::class])->group(function () {

    // 🧭 Route impersonasi dashboard untuk admin dan staff
    Route::get('/role-switch', [RoleSwitchController::class, 'showForm'])->name('role.switch');
    Route::post('/role-switch', [RoleSwitchController::class, 'switch'])->name('role.switch.update');
    Route::middleware(['checkrole:student'])->get('/student/dashboard', fn() => view('student.dashboard'))->name('student.dashboard');
    Route::middleware(['checkrole:staff'])->get('/staff/dashboard', fn() => view('staff.dashboard'))->name('staff.dashboard');
    Route::middleware(['checkrole:general'])->get('/dashboard', fn() => view('general.dashboard'))->name('general.dashboard');


    // ðŸ”¹ Admin Routes
    Route::middleware(['checkrole:admin'])->group(function () {


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
        Route::delete('/admin/setting/mfa/{id}', [UserDeviceController::class, 'Admindelete'])->name('profile.admin.mfa.delete');
        Route::post('/admin/setting/mfa/{id}/trust', [UserDeviceController::class, 'Admintrust'])->name('profile.admin.mfa.trust');
        Route::post('/admin/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Adminuntrust'])->name('profile.admin.mfa.untrust');
        Route::get('/admin/external/setting/mfa', [ProfileController::class, 'adminmfasettingexternal'])->name('profile.admin.mfa.external');
        Route::post('/profile/admin/toggle-passwordless', [ProfileController::class, 'adminTogglePasswordless'])->name('profile.admin.toggle-passwordless');
        Route::get('/admin/logs', [LogViewerController::class, 'index'])->name('admin.logs');
        Route::post('/admin/setting/extended-mfa-setting', [ExtendedMfaController::class, 'updateSettingAdmin'])->name('profile.admin.extended-mfa.setting');
        // LDAP Access Challenge (OTP + MFA + Foto)
        Route::get('/admin/setting/manage-user/ldap/verify-access', [OtpLdapVerificationController::class, 'form'])->name('ldap.otp.form');
        Route::post('/admin/setting/manage-user/ldap/verify-access', [OtpLdapVerificationController::class, 'verify'])->name('ldap.otp.verify');
        Route::post('/admin/setting/manage-user/ldap/verify-access/resend', [OtpLdapVerificationController::class, 'resend'])->name('ldap.otp.resend');
        Route::post('/admin/setting/manage-user/ldap/verify-access/cancel', [OtpLdapVerificationController::class, 'cancel'])->name('ldap.otp.cancel');

        // LDAP routes yang dibatasi MFA OTP
        Route::middleware(['ensure.ldap.otp'])->group(function () {
            Route::get('/admin/setting/manage-user/ldap', [LdapManageController::class, 'index'])->name('ldap.index');
            Route::post('/admin/setting/manage-user/ldap', [LdapManageController::class, 'store'])->name('ldap.store');
            Route::delete('/admin/setting/manage-user/ldap/delete', [LdapManageController::class, 'destroy'])->name('ldap.delete');
            Route::put('/admin/setting/manage-user/ldap/update', [LdapManageController::class, 'update'])->name('ldap.update');
        });



    });

    // ðŸ”¹ Student Routes
    Route::middleware(['checkrole:student'])->group(function () {
        Route::get('/student/dashboard', [HomeController::class, 'indexStudent'])->name('student.dashboard');
        Route::get('/student/setting', [ProfileController::class, 'studentprofile'])->name('profile.student.setting');
        Route::get('/student/setting/profile', [ProfileController::class, 'studenteditprofile'])->name('profile.student.profile');
        Route::get('/student/setting/mfa', [ProfileController::class, 'studentmfasetting'])->name('profile.student.mfa');

        Route::get('/student/setting/session', [SessionController::class, 'Studentshow'])->name('profile.student.session.show');
        Route::delete('/student/setting/session/{id}', [SessionController::class, 'Studentrevoke'])->name('profile.student.session.revoke');
        Route::post('/student/setting/session/revoke-all', [SessionController::class, 'StudentrevokeAll'])->name('profile.student.session.revokeAll');

        Route::delete('/student/setting/mfa/{id}', [UserDeviceController::class, 'Studentdelete'])->name('profile.student.mfa.delete');
        Route::post('/student/setting/mfa/{id}/trust', [UserDeviceController::class, 'Studenttrust'])->name('profile.student.mfa.trust');
        Route::post('/student/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Studentuntrust'])->name('profile.student.mfa.untrust');

        // Student External MFA Route
        Route::get('/student/external/setting/mfa', [ProfileController::class, 'studentmfasettingexternal'])->name('profile.student.mfa.external');

        Route::post('/profile/student/toggle-passwordless', [ProfileController::class, 'studentTogglePasswordless'])->name('profile.student.toggle-passwordless');
        Route::post('/student/setting/extended-mfa-setting', [ExtendedMfaController::class, 'updateSettingStudent'])->name('profile.student.extended-mfa.setting');


    });

    // ðŸ”¹ Staff Routes
    Route::middleware(['checkrole:staff'])->group(function () {
        Route::get('/staff/dashboard', [HomeController::class, 'indexStaff'])->name('staff.dashboard');
        Route::get('/staff/setting', [ProfileController::class, 'staffprofile'])->name('profile.staff.setting');
        Route::get('/staff/setting/profile', [ProfileController::class, 'staffeditprofile'])->name('profile.staff.profile');
        Route::get('/staff/setting/mfa', [ProfileController::class, 'staffmfasetting'])->name('profile.staff.mfa');

        Route::get('/staff/setting/session', [SessionController::class, 'Staffshow'])->name('profile.staff.session.show');
        Route::delete('/staff/setting/session/{id}', [SessionController::class, 'Staffrevoke'])->name('profile.staff.session.revoke');
        Route::post('/staff/setting/session/revoke-all', [SessionController::class, 'StaffrevokeAll'])->name('profile.staff.session.revokeAll');

        Route::delete('/staff/setting/mfa/{id}', [UserDeviceController::class, 'Staffdelete'])->name('profile.staff.mfa.delete');
        Route::post('/staff/setting/mfa/{id}/trust', [UserDeviceController::class, 'Stafftrust'])->name('profile.staff.mfa.trust');
        Route::post('/staff/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Staffuntrust'])->name('profile.staff.mfa.untrust');

        // Staff External MFA Route
        Route::get('/staff/external/setting/mfa', [ProfileController::class, 'staffmfasettingexternal'])->name('profile.staff.mfa.external');

        Route::post('/profile/staff/toggle-passwordless', [ProfileController::class, 'staffTogglePasswordless'])->name('profile.staff.toggle-passwordless');
        Route::post('/staff/setting/extended-mfa-setting', [ExtendedMfaController::class, 'updateSettingStaff'])->name('profile.staff.extended-mfa.setting');
    });

    // ðŸ”¹ General User Routes
    Route::middleware(['checkrole:general'])->group(function () {
        Route::get('/dashboard', function () {
            $showMfaReminder = Auth::check() && optional(Auth::user()->mfa)->mfa_enabled != 1;
            return view('dashboard', compact('showMfaReminder'));
        })->name('dashboard');
        Route::get('/setting', [ProfileController::class, 'profile'])->name('profile.setting');
        Route::get('/setting/profile', [ProfileController::class, 'editprofile'])->name('profile.profile');
        Route::get('/setting/mfa', [ProfileController::class, 'mfasetting'])->name('profile.mfa');
        Route::get('/setting/session', [SessionController::class, 'show'])->name('profile.session.show');
        Route::delete('/setting/session/{id}', [SessionController::class, 'revoke'])->name('profile.session.revoke');
        Route::post('/setting/session/revoke-all', [SessionController::class, 'revokeAll'])->name('profile.session.revokeAll');
        Route::delete('/setting/mfa/{id}', [UserDeviceController::class, 'Generaldelete'])->name('profile.mfa.delete');
        Route::post('/setting/mfa/{id}/trust', [UserDeviceController::class, 'Generaltrust'])->name('profile.mfa.trust');
        Route::post('/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Generaluntrust'])->name('profile.mfa.untrust');
        // General External MFA Route
        Route::get('/external/setting/mfa', [ProfileController::class, 'mfasettingexternal'])->name('profile.mfa.external');
        Route::post('/profile/toggle-passwordless', [ProfileController::class, 'generalTogglePasswordless'])->name('profile.toggle-passwordless');
        Route::post('/profile/extended-mfa-setting', [ExtendedMfaController::class, 'updateSetting'])->name('profile.extended-mfa.setting');
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
Route::post('/customer-support/send', [SupportController::class, 'sendEmail'])
    ->middleware('throttle:5,10') // Maks. 5x request per 10 menit
    ->name('customer-support.send');
Route::put('/profile/update-phone', [ProfileController::class, 'updatePhone'])->name('profile.update.phone');

// LDAP Registration
Route::middleware('throttle:5,1')->group(function () {
    Route::get('/ldap-register', [LdapRegisterController::class, 'showForm'])->name('ldap.register');
    Route::post('/ldap-register', [LdapRegisterController::class, 'register']);
});

// Passwordless Login
Route::get('/passwordless/request', [AuthenticatedSessionController::class, 'showPasswordlessForm'])->name('passwordless.request');
Route::post('/passwordless/request', [AuthenticatedSessionController::class, 'sendMagicLink'])->name('passwordless.send');
Route::get('/passwordless/verify/{token}', [AuthenticatedSessionController::class, 'verifyMagicLink'])->name('passwordless.verify');

// aplikasi bap
Route::get('/sso/bap-re', [SsoController::class, 'redirectToBap'])->middleware(['auth', 'ensure.extended.mfa'])->name('sso.to.bap');
Route::get('/from-bap', function () {
    $user = Auth::user();
    $active = session('active_role', $user->usertype);

    // Jika role aktif tidak sama dengan usertype, reset session dan redirect ke default
    if ($active !== $user->usertype) {
        session(['active_role' => $user->usertype]);
    }

    return redirect()->route(match ($user->usertype) {
        'admin' => 'admin.dashboard',
        'staff' => 'staff.dashboard',
        'student' => 'student.dashboard',
        default => 'dashboard',
    });
})->middleware(['auth']);
Route::get('/from-bap/setting', function () {
    $user = Auth::user();
    $active = session('active_role', $user->usertype);

    if ($active !== $user->usertype) {
        session(['active_role' => $user->usertype]);
    }

    return redirect()->route(match ($user->usertype) {
        'admin' => 'profile.admin.setting',
        'staff' => 'profile.staff.setting',
        'student' => 'profile.student.setting',
        default => 'profile.setting',
    });
})->middleware(['auth'])->name('from.bap.setting');
Route::get('/from-bap/support', function () {
    return redirect()->route('customer-support');
})->name('from.bap.support');
Route::get('/force-logout', function () {
    Auth::logout();
    Session::flush();
    return redirect()->route('login');
});
Route::middleware(['auth', 'ensure.extended.mfa'])->group(function () {
    Route::get('/sso/bap-direct', fn() => redirect()->away('https://bap.petra.ac.id'))->name('sso.to.bap.new');
    Route::get('/sso/leap', fn() => redirect()->away('https://leap.petra.ac.id'))->name('sso.to.leap');
    Route::get('/sso/obe', fn() => redirect()->away('https://leap.petra.ac.id'))->name('sso.to.obe');
    Route::get('/sso/bimbingan-mahasiswa', fn() => redirect()->away('https://leap.petra.ac.id'))->name('sso.to.bimbingan');
    Route::get('/sso/service-learning', fn() => redirect()->away('https://service-learning.petra.ac.id'))->name('sso.to.service');
    Route::get('/sso/sim', fn() => redirect()->away('https://sim.petra.ac.id'))->name('sso.to.sim');
    Route::get('/sso/sim-eltc', fn() => redirect()->away('https://sim-eltc.petra.ac.id'))->name('sso.to.sim-eltc');
    Route::get('/sso/tnc', fn() => redirect()->away('https://tnc.petra.ac.id'))->name('sso.to.tnc');
    Route::get('/sso/form', fn() => redirect()->away('https://form.petra.ac.id'))->name('sso.to.form');
    Route::get('/sso/sister', fn() => redirect()->away('https://sister.kemdikbud.go.id/beranda'))->name('sso.to.sister');
    Route::get('/sso/event', fn() => redirect()->away('https://events.petra.ac.id'))->name('sso.to.event');
    Route::get('/sso/uppk', fn() => redirect()->away('https://uppk.petra.ac.id'))->name('sso.to.uppk');
    Route::get('/sso/shortener', fn() => redirect()->away('https://s.petra.ac.id'))->name('sso.to.shortener');
    Route::get('/sso/lostnfound', fn() => redirect()->away('http://lostnfound.petra.ac.id'))->name('sso.to.lost');
    Route::get('/sso/konseling', fn() => redirect()->away('https://konseling.petra.ac.id'))->name('sso.to.konseling');
    Route::get('/sso/library', fn() => redirect()->away('https://konseling.petra.ac.id'))->name('sso.to.library');
    Route::get('/sso/grant', fn() => redirect()->away('https://tnc.petra.ac.id'))->name('sso.to.grant');
    Route::get('/sso/alumni', fn() => redirect()->away('https://alumni.petra.ac.id/'))->name('sso.to.alumni');
    Route::get('/sso/survey', fn() => redirect()->away('https://survey.petra.ac.id'))->name('sso.to.survey');
    Route::get('/sso/survei-alumni', fn() => redirect()->away('https://survei-alumni.petra.ac.id'))->name('sso.to.survei-alumni');
    Route::get('/sso/penugasan', fn() => redirect()->away('https://survei-alumni.petra.ac.id'))->name('sso.to.penugasan');
    Route::get('/sso/akreditasi', fn() => redirect()->away('https://sim-eltc.petra.ac.id'))->name('sso.to.akreditasi');
    Route::get('/sso/hsep', fn() => redirect()->away('https://sim.petra.ac.id'))->name('sso.to.hsep');
});
