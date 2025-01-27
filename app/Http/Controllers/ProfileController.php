<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function studentprofile(Request $request): View
    {
        return view('profile.student.setting', [
            'user' => $request->user(),
        ]);
    }
    public function staffprofile(Request $request): View
    {
        return view('profile.staff.setting', [
            'user' => $request->user(),
        ]);
    }
    public function profile(Request $request): View
    {
        return view('profile.setting', [
            'user' => $request->user(),
        ]);
    }
    public function studenteditprofile(Request $request): View
    {
        return view('profile.student.profile', [
            'user' => $request->user(),
        ]);
    }
    public function staffeditprofile(Request $request): View
    {
        return view('profile.staff.profile', [
            'user' => $request->user(),
        ]);
    }
    public function editprofile(Request $request): View
    {
        return view('profile.profile', [
            'user' => $request->user(),
        ]);
    }
    public function studentsession(Request $request): View
    {
        return view('profile.student.session', [
            'user' => $request->user(),
        ]);
    }
    public function staffsession(Request $request): View
    {
        return view('profile.staff.session', [
            'user' => $request->user(),
        ]);
    }
    public function editsession(Request $request): View
    {
        return view('profile.session', [
            'user' => $request->user(),
        ]);
    }
}
