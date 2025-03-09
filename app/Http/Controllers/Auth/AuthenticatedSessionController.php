<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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
    // Check if the request contains emailLocalPart (for Student/Staff/Admin)
    $email = $request->has('emailLocalPart') 
        ? $request->emailLocalPart . $request->emailDomain 
        : $request->email;

    $credentials = [
        'email' => $email,
        'password' => $request->password,
    ];

    // Attempt database login first
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return $this->redirectUser(Auth::user());
    }

    // Attempt LDAP authentication if database login fails
    $ldapUser = LdapUser::where('mail', $credentials['email'])->first();

    if ($ldapUser && $ldapUser->authenticate($credentials['password'])) {
        // Sync LDAP user into Laravel database
        $user = User::updateOrCreate(
            ['email' => $ldapUser->mail[0]],
            [
                'name' => $ldapUser->cn[0] ?? 'Unknown',
                'password' => Hash::make($credentials['password']),
                'usertype' => 'general', // Default role for LDAP users
            ]
        );

        Auth::login($user);
        return $this->redirectUser($user);
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
}


    /**
     * Redirect user based on user type.
     */
    protected function redirectUser(User $user): RedirectResponse
    {
        $userType = $user->usertype;

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
