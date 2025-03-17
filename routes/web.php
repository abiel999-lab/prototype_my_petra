<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;
use App\Http\Middleware\StoreUserSession;
use App\Http\Controllers\UserDeviceController; // Added UserDeviceController
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Carbon\Carbon;

// ðŸ”¹ Redirect root URL ('/') to the correct dashboard or login
Route::get('/', function () {
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

        if (!$user) {
            // Create new user with 'general' as default usertype
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(uniqid()), // Random password
                'usertype' => 'general', // Default user type (only set on creation)
            ]);
        } else {
            // Update only Google ID to link the account, but keep usertype
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);
        }

        Auth::login($user);

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
        Log::error('Google OAuth Error: ' . $e->getMessage()); // Log error for debugging
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
            $user = User::updateOrCreate(
                ['email' => $ldapUser->mail[0]],
                [
                    'name' => $ldapUser->cn[0] ?? 'Unknown',
                    'password' => Hash::make($credentials['password']),
                    'usertype' => 'general',
                ]
            );

            Auth::login($user);

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
        Log::error('LDAP Authentication Error: ' . $e->getMessage());
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
    Route::post('/mfa-challenge/send-otp', [TwoFactorController::class, 'handleWhatsAppOtp'])
        ->name('mfa-challenge.send-otp');

});

// ðŸ”¹ Authenticated Routes (Protected by MFA & Session Middleware)
Route::middleware(['auth', 'mfachallenge', StoreUserSession::class])->group(function () {

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


        Route::get('/admin/setting/mfa', [UserDeviceController::class, 'Adminindex'])->name('profile.admin.mfa');
        Route::delete('/admin/setting/mfa/{id}', [UserDeviceController::class, 'Admindelete'])->name('profile.admin.mfa.delete');
        Route::post('/admin/setting/mfa/{id}/trust', [UserDeviceController::class, 'Admintrust'])->name('profile.admin.mfa.trust');
        Route::post('/admin/setting/mfa/{id}/untrust', [UserDeviceController::class, 'Adminuntrust'])->name('profile.admin.mfa.untrust');
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

    });

});


// ðŸ”¹ Authentication Routes (Login & Authentication)
require __DIR__ . '/auth.php';

// ðŸ”¹ Public Login Pages
Route::get('/login/public', [AuthenticatedSessionController::class, 'createPublic'])->name('login.public');
Route::get('/login/admin', [AuthenticatedSessionController::class, 'createAdmin'])->name('login.admin');

// ðŸ”¹ Email & Password Check
Route::post('/check-email-password', [AuthController::class, 'checkEmailAndPassword'])->name('checkEmailAndPassword');
Route::get('/customer-support', [SupportController::class, 'index'])->name('customer-support');
Route::post('/customer-support/send', [SupportController::class, 'sendEmail'])->name('customer-support.send');
Route::put('/profile/update-phone', [ProfileController::class, 'updatePhone'])->name('profile.update.phone');
