<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }

    public function adminAttendanceList(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date'))->toDateString() : now()->toDateString();
        $attendances = Attendance::whereDate('date', $date)->with('user', 'breakTimes')->get();

        return view('admin.admin_attendance_list', compact('attendances', 'date'));
    }
}
