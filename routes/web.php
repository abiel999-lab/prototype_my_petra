<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;

// Redirect root URL ('/') to the correct dashboard or login
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
                return redirect()->route('dashboard'); // Redirect 'general' users here
            default:
                return redirect()->route('dashboard'); // Default for public users
        }
    }
    return redirect()->route('login'); // If not logged in, redirect to login
})->name('home');

// Authentication Middleware
Route::middleware('auth')->group(function () {
    // MFA Challenge Routes
    Route::get('/mfa-challenge', [TwoFactorController::class, 'index'])->name('mfa-challenge.index');
    Route::post('/mfa-challenge', [TwoFactorController::class, 'verify'])->name('mfa-challenge.verify');

    // MFA Settings
    Route::post('/toggle-mfa', [ProfileController::class, 'toggleMfa'])->name('toggle-mfa');
    Route::post('/set-mfa-method', [ProfileController::class, 'setMfaMethod'])->name('set-mfa-method');
});

// Authenticated & MFA Middleware Group (Protects all dashboard and settings pages)
Route::middleware(['auth', 'mfachallenge'])->group(function () {

    // 🔹 Admin Routes (Restricted to Admins)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [HomeController::class, 'indexAdmin'])->name('admin.dashboard');
        Route::get('/admin/setting', [ProfileController::class, 'adminprofile'])->name('profile.admin.setting');
        Route::get('/admin/setting/profile', [ProfileController::class, 'admineditprofile'])->name('profile.admin.profile');
        Route::get('/admin/setting/session', [ProfileController::class, 'adminsession'])->name('profile.admin.session');
        Route::get('/admin/setting/mfa', [ProfileController::class, 'adminmfasetting'])->name('profile.admin.mfa');
        Route::get('/admin/setting/manage-user', [UserController::class, 'index'])->name('profile.admin.manageuser');
        Route::post('/admin/setting/manage-user/store', [UserController::class, 'store'])->name('profile.admin.manageuser.store');
        Route::put('/admin/setting/manage-user/update/{user}', [UserController::class, 'update'])->name('profile.admin.manageuser.update');
        Route::delete('/admin/setting/manage-user/delete/{user}', [UserController::class, 'destroy'])->name('profile.admin.manageuser.delete');
    });

    // 🔹 Student Routes (Restricted to Students)
    Route::middleware(['role:student'])->group(function () {
        Route::get('/student/dashboard', [HomeController::class, 'indexStudent'])->name('student.dashboard');
        Route::get('/student/setting', [ProfileController::class, 'studentprofile'])->name('profile.student.setting');
        Route::get('/student/setting/profile', [ProfileController::class, 'studenteditprofile'])->name('profile.student.profile');
        Route::get('/student/setting/session', [ProfileController::class, 'studentsession'])->name('profile.student.session');
        Route::get('/student/setting/mfa', [ProfileController::class, 'studentmfasetting'])->name('profile.student.mfa');
    });

    // 🔹 Staff Routes (Restricted to Staff)
    Route::middleware(['role:staff'])->group(function () {
        Route::get('/staff/dashboard', [HomeController::class, 'indexStaff'])->name('staff.dashboard');
        Route::get('/staff/setting', [ProfileController::class, 'staffprofile'])->name('profile.staff.setting');
        Route::get('/staff/setting/profile', [ProfileController::class, 'staffeditprofile'])->name('profile.staff.profile');
        Route::get('/staff/setting/session', [ProfileController::class, 'staffsession'])->name('profile.staff.session');
        Route::get('/staff/setting/mfa', [ProfileController::class, 'staffmfasetting'])->name('profile.staff.mfa');
    });

    // 🔹 General User Routes (Restricted to General Users)
    Route::middleware(['role:general'])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        Route::get('/setting', [ProfileController::class, 'profile'])->name('profile.setting');
        Route::get('/setting/profile', [ProfileController::class, 'editprofile'])->name('profile.profile');
        Route::get('/setting/mfa', [ProfileController::class, 'mfasetting'])->name('profile.mfa');
        Route::get('/setting/session', [ProfileController::class, 'editsession'])->name('profile.session');
    });

});

// Auth Routes (Login & Authentication)
require __DIR__ . '/auth.php';

// Public Login Pages
Route::get('/login/public', [AuthenticatedSessionController::class, 'createPublic'])->name('login.public');
Route::get('/login/admin', [AuthenticatedSessionController::class, 'createAdmin'])->name('login.admin');

// Email & Password Check for Login
Route::post('/check-email-password', [AuthController::class, 'checkEmailAndPassword'])->name('checkEmailAndPassword');
