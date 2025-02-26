<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');
    $mfaEnabled = $request->input('mfa_enabled');
    $mfaMethod = $request->input('mfa_method');
    $userType = $request->input('usertype');

    $users = User::query();

    // Regular Search (by name or email)
    if ($search) {
        $users->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // MFA Enabled Filter (yes/no or on/off)
    if ($mfaEnabled === "on" || $mfaEnabled === "yes" || $mfaEnabled === "1") {
        $users->where('mfa_enabled', 1);
    } elseif ($mfaEnabled === "off" || $mfaEnabled === "no" || $mfaEnabled === "0") {
        $users->where('mfa_enabled', 0);
    }

    // MFA Method Filter (email or google_authenticator)
    if ($mfaMethod) {
        $users->where('mfa_method', $mfaMethod);
    }

    // User Type Filter (admin, student, staff, general)
    if ($userType) {
        $users->where('usertype', $userType);
    }

    $users = $users->paginate(5);

    return view('profile.admin.manage-user', compact('users', 'search'));
}


    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
        'usertype' => 'required|string|in:admin,staff,student,general'
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'usertype' => $request->usertype, // Ensure usertype is stored correctly
    ]);

    return redirect()->route('profile.admin.manageuser')->with('success', 'User created successfully.');
}




    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'usertype' => 'required|string|in:general,admin,staff,student',
            'mfa_enabled' => 'nullable|boolean',
            'mfa_method' => 'required|string|in:email,google_authenticator',
        ]);

        // Convert checkbox value to boolean (1 or 0)
        $mfa_enabled = $request->input('mfa_enabled', 0); // Default to 0 if not present

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'usertype' => $request->usertype,
            'mfa_enabled' => $mfa_enabled,
            'mfa_method' => $request->mfa_method,
        ]);

        return redirect()->route('profile.admin.manageuser')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('profile.admin.manageuser')->with('success', 'User deleted successfully.');
    }
}
