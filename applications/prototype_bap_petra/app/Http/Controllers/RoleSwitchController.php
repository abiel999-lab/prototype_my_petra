<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RoleSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $user = Auth::user();
        $usertype = $user->usertype;
        $targetRole = $request->input('role');

        $isValid = $targetRole === $usertype || $user->roles()->where('name', $targetRole)->exists();

        if (!$isValid) {
            return back()->with('error', 'You are not authorized to switch to this role.');
        }

        session(['active_role' => $targetRole]);

        // Selalu redirect ke dashboard yang sama
        return redirect()->route('dashboard');
    }
}
