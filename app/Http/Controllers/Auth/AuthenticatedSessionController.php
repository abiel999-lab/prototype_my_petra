<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Illuminate\View\View;

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
        $request->authenticate();
        $request->session()->regenerate();

        $userType = $request->user()->usertype;

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
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
