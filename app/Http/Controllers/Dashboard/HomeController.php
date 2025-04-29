<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function indexStaff()
    {

        return view('staff.dashboard');
    }
    public function indexStudent()
    {

        return view('student.dashboard');
    }
    public function indexAdmin()
    {
        // Kalau tidak ada, tampilkan dashboard admin asli
        return view('admin.dashboard');
    }



}
