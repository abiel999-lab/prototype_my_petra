<?php

namespace App\Http\Controllers\UserManagement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RoleSwitchController extends Controller
{
    /**
     * Tampilkan form untuk mengganti role aktif.
     */
    public function showForm()
    {
        $user = Auth::user();
        $usertype = $user->usertype;

        // Ambil semua role tambahan selain usertype asli
        $availableRoles = $user->roles->pluck('name')->filter(fn($role) => $role !== $usertype);

        // Jika tidak punya role tambahan, jangan tampilkan form
        if ($availableRoles->isEmpty()) {
            abort(403, 'You do not have any additional role access.');
        }

        return view('profile.role-switch', [
            'current_role' => session('active_role', $usertype),
            'default_role' => $usertype,
            'available_roles' => $availableRoles,
        ]);
    }

    /**
     * Proses pergantian role berdasarkan input user.
     */
    public function switch(Request $request)
    {
        $user = Auth::user();
        $usertype = $user->usertype;
        $targetRole = $request->input('role');

        // Hanya boleh switch ke usertype sendiri atau ke role yang dimiliki via pivot
        $isValid = $targetRole === $usertype
            || $user->roles()->where('name', $targetRole)->exists();

        if (!$isValid) {
            return back()->with('error', 'You are not authorized to switch to this role.');
        }

        // Simpan role aktif di session
        session(['active_role' => $targetRole]);

        // Redirect ke dashboard sesuai role yang dipilih
        return redirect()->route(match ($targetRole) {
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            'student' => 'student.dashboard',
            'general' => 'dashboard',
            default => 'dashboard',
        });
    }
}
