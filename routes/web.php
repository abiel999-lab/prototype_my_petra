<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

// Redirect root URL ('/') to the login route
Route::get('/', function () {
    return redirect()->route('login'); // Redirect to the 'login' route
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/setting', [ProfileController::class, 'profile'])->name('profile.setting');
    Route::get('student/setting', [ProfileController::class, 'studentprofile'])->name('profile.student.setting');
    Route::get('staff/setting', [ProfileController::class, 'staffprofile'])->name('profile.staff.setting');
    Route::get('/setting/profile', [ProfileController::class, 'editprofile'])->name('profile.profile');
    Route::get('student/setting/profile', [ProfileController::class, 'studenteditprofile'])->name('profile.student.profile');
    Route::get('staff/setting/profile', [ProfileController::class, 'staffeditprofile'])->name('profile.staff.profile');
    Route::get('/setting/session', [ProfileController::class, 'editprofile'])->name('profile.session');
    Route::get('student/setting/session', [ProfileController::class, 'studentsession'])->name('profile.student.session');
    Route::get('staff/setting/session', [ProfileController::class, 'staffsession'])->name('profile.staff.session');
});


require __DIR__ . '/auth.php';

route::get('staff/dashboard',[HomeController::class,'indexStaff'])->name('staff.dashboard');
route::get('student/dashboard',[HomeController::class,'indexStudent'])->name('student.dashboard');
Route::get('/login/public', [AuthenticatedSessionController::class, 'createPublic'])->name('login.public');
Route::post('/check-email-password', [AuthController::class, 'checkEmailAndPassword'])->name('checkEmailAndPassword');









