<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;

// Redirect root URL ('/') to the login route
Route::get('/', function () {
    return redirect()->route('login'); // Redirect to the 'login' route
});



Route::middleware('auth')->group(function () {

    Route::get('/mfa-challenge', [TwoFactorController::class, 'index'])->name('mfa-challenge.index');
    Route::post('/mfa-challenge', [TwoFactorController::class, 'verify'])->name('mfa-challenge.verify');
    // Route for toggling MFA
    Route::post('/toggle-mfa', [ProfileController::class, 'toggleMfa'])->name('toggle-mfa');
    Route::post('/set-mfa-method', [ProfileController::class, 'setMfaMethod'])->name('set-mfa-method');



});


Route::middleware(['auth', 'mfachallenge'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    route::get('staff/dashboard',[HomeController::class,'indexStaff'])->name('staff.dashboard');
    route::get('student/dashboard',[HomeController::class,'indexStudent'])->name('student.dashboard');
    route::get('admin/dashboard',[HomeController::class,'indexAdmin'])->name('admin.dashboard');
    Route::get('/setting', [ProfileController::class, 'profile'])->name('profile.setting');
    Route::get('student/setting', [ProfileController::class, 'studentprofile'])->name('profile.student.setting');
    Route::get('staff/setting', [ProfileController::class, 'staffprofile'])->name('profile.staff.setting');
    Route::get('admin/setting', [ProfileController::class, 'adminprofile'])->name('profile.admin.setting');

    Route::get('/setting/profile', [ProfileController::class, 'editprofile'])->name('profile.profile');
    Route::get('student/setting/profile', [ProfileController::class, 'studenteditprofile'])->name('profile.student.profile');
    Route::get('staff/setting/profile', [ProfileController::class, 'staffeditprofile'])->name('profile.staff.profile');
    Route::get('admin/setting/profile', [ProfileController::class, 'admineditprofile'])->name('profile.admin.profile');

    Route::get('/setting/session', [ProfileController::class, 'editsession'])->name('profile.session');
    Route::get('student/setting/session', [ProfileController::class, 'studentsession'])->name('profile.student.session');
    Route::get('staff/setting/session', [ProfileController::class, 'staffsession'])->name('profile.staff.session');
    Route::get('admin/setting/session', [ProfileController::class, 'adminsession'])->name('profile.admin.session');

    Route::get('/setting/mfa', [ProfileController::class, 'mfasetting'])->name('profile.mfa');
    Route::get('student/setting/mfa', [ProfileController::class, 'studentmfasetting'])->name('profile.student.mfa');
    Route::get('staff/setting/mfa', [ProfileController::class, 'staffmfasetting'])->name('profile.staff.mfa');
    Route::get('admin/setting/mfa', [ProfileController::class, 'adminmfasetting'])->name('profile.admin.mfa');

    Route::get('admin/setting/manageuser', [ProfileController::class, 'manageuser'])->name('profile.admin.manageuser');

});


require __DIR__ . '/auth.php';
Route::get('/login/public', [AuthenticatedSessionController::class, 'createPublic'])->name('login.public');
Route::get('/login/admin', [AuthenticatedSessionController::class, 'createAdmin'])->name('login.admin');
Route::post('/check-email-password', [AuthController::class, 'checkEmailAndPassword'])->name('checkEmailAndPassword');







