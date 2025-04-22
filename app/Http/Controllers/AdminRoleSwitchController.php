<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoleSwitchController extends Controller
{
    public function showForm()
    {
        $user = Auth::user();

        // Jangan izinkan jika user bukan admin asli
        if ($user->usertype !== 'admin') {
            abort(403);
        }

        return view('admin.switch-role');
    }

    public function switch(Request $request)
    {
        $user = Auth::user();

        if ($user->usertype !== 'admin') {
            abort(403);
        }

        $role = $request->input('temporary_role');

        $validRoles = ['student', 'staff', 'general', null, '']; // '' artinya kembali ke admin

        if (!in_array($role, $validRoles, true)) {
            return back()->with('error', 'Invalid role');
        }

        // Ubah temporary role
        $user->temporary_role = $role === '' ? null : $role;
        $user->save();

        return redirect()->route(match ($user->temporary_role) {
            'student' => 'student.dashboard',
            'staff' => 'staff.dashboard',
            'general' => 'dashboard',
            default => 'admin.dashboard'
        });
    }

}
