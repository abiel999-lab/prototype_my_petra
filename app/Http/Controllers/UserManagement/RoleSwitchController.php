<?php

namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RoleSwitchController extends Controller
{
    public function showForm()
    {
        $user = Auth::user();
        $role = $user->usertype;

        // Hanya admin dan staff yang boleh melihat form switch
        if (!in_array($role, ['admin', 'staff'])) {
            abort(403);
        }

        return view('admin.switch-role', [
            'current_role' => $role,
        ]);
    }

    public function switch(Request $request)
    {
        $user = Auth::user();
        $role = $user->usertype;

        $target = $request->input('temporary_role');

        // Validasi untuk admin
        if ($role === 'admin') {
            $validTargets = ['student', 'staff', 'general', null, ''];
        }
        // Validasi untuk staff
        elseif ($role === 'staff') {
            $validTargets = ['student', null, ''];
        }
        // User selain admin/staff tidak boleh impersonasi
        else {
            abort(403);
        }

        if (!in_array($target, $validTargets, true)) {
            return back()->with('error', 'Invalid role switch');
        }

        // Simpan temporary role (kosong berarti kembali ke role asli)
        $user->temporary_role = ($target === '' || $target === null) ? null : $target;
        $user->save();

        return redirect()->route(match ($user->temporary_role ?? $user->usertype) {
            'student' => 'student.dashboard',
            'staff' => 'staff.dashboard',
            'general' => 'dashboard',
            'admin' => 'admin.dashboard',
        });
    }

}
