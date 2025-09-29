<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Status;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        $today = Carbon::now();
        $now = $today->isoFormat('YYYY年MM月DD日 (ddd)');
        $latestAttendance = $user->attendances()->latest()->first();
        $statusLabel = $latestAttendance?->status->label ?? '勤務外';

        return view('attendance.attendance', compact('now', 'today', 'statusLabel'));
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now();
        $now = $today->isoFormat('YYYY年MM月DD日 (ddd)');
        $latestAttendance = $user->attendances()->latest()->first();
        $statusLabel = $latestAttendance?->status->label ?? '勤務外';

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
        if (!$attendance) {
            $status = Status::create(['status' => 1]);

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => now(),
                'clock_in' => $request->clock_in ?? now(),
                'status_id' => $status->id,
            ]);
        } else {
            $attendance->update([
                'clock_out' => now(),
            ]);
            $attendance->status()->update(['status' => 3]);
        }

        return redirect(route('attendance.attendance'));
    }
}
