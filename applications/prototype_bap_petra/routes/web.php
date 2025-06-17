<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SSOLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RoleSwitchController;

// 🔁 Redirect root ke dashboard
Route::get('/', fn() => redirect('/dashboard'));

// 🔐 Endpoint untuk menangani login dari SSO (tanpa middleware)
Route::get('/sso-login', [SSOLoginController::class, 'handleSSOLogin'])->name('sso.login');

// ✅ Route yang hanya bisa diakses jika sudah login dan berasal dari menu utama
Route::middleware(['auth', 'from.main'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
});

// 🧑‍💼 Route untuk pengelolaan profil (autentikasi biasa)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


Route::get('/force-logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
});

Route::post('/switch-role', [RoleSwitchController::class, 'switch'])->name('role.switch.update');

Route::get('/from-bap/setting', function () {
    return view('profile.profile');
})->name('app.settings');


// 📦 Route auth dari Breeze
require __DIR__ . '/auth.php';

