<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function indexStaff(){

        return view('staff.dashboard');
    }
    public function indexStudent(){

        return view('student.dashboard');
    }
    public function indexAdmin(){

        return view('admin.dashboard');
    }


}
