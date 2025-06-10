<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function indexStaff()
    {
        $showMfaReminder = auth()->check() &&
            optional(auth()->user()->mfa)->mfa_enabled != 1;

        return view('staff.dashboard',compact('showMfaReminder'));
    }
    public function indexStudent()
    {

        $showMfaReminder = auth()->check() &&
            optional(auth()->user()->mfa)->mfa_enabled != 1;
        return view('student.dashboard',compact('showMfaReminder'));
    }
    public function indexAdmin()
    {
        $showMfaReminder = auth()->check() &&
            optional(auth()->user()->mfa)->mfa_enabled != 1;
        // Kalau tidak ada, tampilkan dashboard admin asli
        return view('admin.dashboard',compact('showMfaReminder'));
    }



}
