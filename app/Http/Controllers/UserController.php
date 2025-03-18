<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\TrustedDevice;
use Jenssegers\Agent\Agent;

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

        // Fetch OS details for each user's devices
        foreach ($users as $user) {
            $user->devices = TrustedDevice::where('user_id', $user->id)->get();

            foreach ($user->devices as $device) {
                $agent = new Agent();
                $agent->setUserAgent($device->user_agent);
                $device->os = $agent->platform(); // Extract OS from user agent
            }
        }

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
            'mfa_method' => 'required|string|in:email,google_authenticator,sms,sms2',
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
    public function ban(User $user)
    {
        if ($user->banned_status) {
            return response()->json(["success" => false, "message" => "User is already banned."]);
        }

        $user->banned_status = 1; // Ban user
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "User has been banned successfully."
        ]);
    }

    public function unban(User $user)
    {
        if (!$user->banned_status) {
            return response()->json(["success" => false, "message" => "User is not banned."]);
        }

        $user->banned_status = 0; // Unban user
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "User has been unbanned successfully."
        ]);
    }

}
