<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }

    public function adminAttendanceList()
    {
        return view('admin.admin_attendance_list');
    }
}
