<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserManagement\UserDeviceController;
use App\Models\User;
use App\Services\LdapGoogleSyncService;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;


class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            Log::info('Google OAuth callback START');

            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google user fetched', [
                'email' => $googleUser->getEmail(),
                'id'    => $googleUser->getId(),
            ]);

            // 1) Sinkron ke LDAP (dibungkus try-catch sendiri)
            try {
                $ldapSync = app(LdapGoogleSyncService::class);
                $ldapSync->syncFromGoogle($googleUser);

                Log::info('LDAP sync from Google SUCCESS', [
                    'email' => $googleUser->getEmail(),
                ]);
            } catch (\Throwable $e) {
                Log::error('LDAP sync from Google FAILED: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // 2) Sync ke tabel users lokal
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                if ($user->banned_status) {
                    Log::warning('Google OAuth login blocked, user banned', [
                        'email' => $user->email,
                    ]);

                    return redirect()->route('login')->withErrors([
                        'email' => "Your account is banned. Please contact support."
                    ]);
                }

                $email      = $googleUser->getEmail();
                $lowerEmail = strtolower($email);
                $usertype   = 'general';

                if (str_ends_with($lowerEmail, '@john.petra.ac.id')) {
                    $usertype = 'student';
                } elseif (str_ends_with($lowerEmail, '@alumni.petra.ac.id')) {
                    $usertype = 'student';
                } elseif (str_ends_with($lowerEmail, '@peter.petra.ac.id')) {
                    $usertype = 'staff';
                } elseif (str_ends_with($lowerEmail, '@petra.ac.id')) {
                    $usertype = 'staff';
                }

                $user->update([
                    'google_id' => $googleUser->getId(),
                    'usertype'  => $usertype, // 🔹 UPDATE ROLE setiap Google login
                ]);

            } else {
                $email      = $googleUser->getEmail();
                $lowerEmail = strtolower($email);
                $usertype   = 'general';

                if (str_ends_with($lowerEmail, '@john.petra.ac.id')) {
                    $usertype = 'student';
                } elseif (str_ends_with($lowerEmail, '@alumni.petra.ac.id')) {
                    $usertype = 'student';
                } elseif (str_ends_with($lowerEmail, '@peter.petra.ac.id')) {
                    $usertype = 'staff';
                } elseif (str_ends_with($lowerEmail, '@petra.ac.id')) {
                    $usertype = 'staff';
                }

                $user = User::create([
                    'name'                  => $googleUser->getName(),
                    'email'                 => $email,
                    'google_id'             => $googleUser->getId(),
                    'password'              => bcrypt(uniqid()),
                    'usertype'              => $usertype,
                    'banned_status'         => false,
                    'failed_login_attempts' => 0,
                ]);

                Log::info('New user created from Google OAuth', [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'type'  => $user->usertype,
                ]);
            }

            // 3) Login ke Laravel
            Auth::guard('web')->login($user, true);
            $request->session()->regenerate();
            session(['active_role' => $user->usertype]);

            Log::info('Laravel Auth login after Google OAuth SUCCESS', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'usertype'  => $user->usertype,
                'auth_check_after_login' => Auth::check(),
            ]);

            // 4) Device limit check
            $userId           = Auth::id();
            $deviceController = new UserDeviceController();

            try {
                $deviceLimitCheck = $deviceController->handleDeviceTracking($userId);

                if ($deviceLimitCheck instanceof \Illuminate\Http\RedirectResponse) {
                    LoggingService::logSecurityViolation("Device limit hit for user [ID: {$userId}] during Google OAuth login");
                    return $deviceLimitCheck;
                }
            } catch (\Throwable $e) {
                Log::error('Device tracking error after Google OAuth: ' . $e->getMessage(), [
                    'user_id' => $userId,
                ]);
            }

            LoggingService::logMfaEvent("Google OAuth login successful for {$user->email}", [
                'user_id'    => $user->id,
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 5) Redirect ke dashboard
            return match ($user->usertype) {
                'admin'   => redirect()->route('admin.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                'staff'   => redirect()->route('staff.dashboard'),
                default   => redirect()->route('dashboard'),
            };
        } catch (\Exception $e) {
            Log::error('Google OAuth error (outer catch): ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            LoggingService::logSecurityViolation("Google OAuth error: " . $e->getMessage());

            // 🔹 Kalau sudah sempat login, jangan lempar balik ke halaman login lagi
            if (Auth::check()) {
                $user = Auth::user();

                return match ($user->usertype) {
                    'admin'   => redirect()->route('admin.dashboard'),
                    'student' => redirect()->route('student.dashboard'),
                    'staff'   => redirect()->route('staff.dashboard'),
                    default   => redirect()->route('dashboard'),
                };
            }

            // 🔹 Kalau memang belum login sama sekali, baru balik ke login
            return redirect()->route('login')->with('error', 'Google authentication failed.');
        }
    }
}

