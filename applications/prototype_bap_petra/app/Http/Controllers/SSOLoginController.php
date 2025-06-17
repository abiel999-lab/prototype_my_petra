<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;

class SSOLoginController extends Controller
{
    public function handleSSOLogin(Request $request): RedirectResponse
    {
        try {
            $data = Crypt::decrypt($request->token);

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => Str::before($data['email'], '@'),
                    'email' => $data['email'],
                    'password' => bcrypt('password-default'), // tidak digunakan
                ]);
            }

            Auth::login($user);
            session(['user_from_menu' => true]);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            // Kamu bisa log error-nya jika perlu
            // logger()->error('SSO failed: ' . $e->getMessage());
            abort(403, 'Invalid or expired SSO token.');
        }
    }
}
