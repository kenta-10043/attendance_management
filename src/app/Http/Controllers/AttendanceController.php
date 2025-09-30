<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Status;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use App\Enums\AttendanceStatus;
use App\Calendars\CalendarView;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        $today = Carbon::now();
        $now = $today->isoFormat('YYYY年MM月DD日 (ddd)');
        $attendance = $user->attendances()->whereDate('date', $today)->first();
        $latestAttendance = $user->attendances()->latest()->first();
        $statusLabel = $latestAttendance?->status->label ?? '勤務外';

        return view('attendance.attendance', compact('now', 'today', 'statusLabel', 'attendance'));
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        $type = $request->input('type');
        if ($type === 'start' && !$attendance) {

            $status = Status::firstOrCreate(['status' => 1]);  // 1 = 勤務中

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => now(),
                'clock_in' => now(),
                'status_id' => $status->id,
            ]);

            return back()->with('message', '出勤しました');
        }

        if (!$attendance)
            return back()->with('message', '出勤していません');

        if ($type === 'break_start' && $attendance->isWorking()) {
            BreakTime::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'start_break' => now(),
            ]);

            $status = Status::firstOrCreate(['status' => 2]);  // 2 = 休憩中
            $attendance->update(['status_id' => $status->id]);
            $attendance->refresh();

            return back()->with('message', '休憩開始しました');
        }

        if ($type === 'break_end' && $attendance->isOnBreak()) {
            $breakTime = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('end_break')
                ->first();

            if ($breakTime) $breakTime->update(['end_break' => now()]);
            $status = Status::firstOrCreate(['status' => 1]);
            $attendance->update(['status_id' => $status->id]);
            $attendance->refresh();
            return back()->with('message', '休憩終了しました');
        }

        if ($type === 'end' && $attendance->isWorking()) {
            $attendance->update([
                'clock_out' => now(),
            ]);

            $status = Status::firstOrCreate(['status' => 3]); // 3 = 退勤済
            $attendance->update(['status_id' => $status->id]);
            $attendance->refresh();

            return back()->with('message', '退勤しました');
        }

        return back()->with('error', '不正な操作です');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now();

        $calendar = new CalendarView($date);
        $title = $calendar->getTitle();
        $currentMonth = $calendar->getDate();
        $days = $calendar->getMonth();
        $attendances = Attendance::where('user_id', $user->id)->whereMonth('clock_in', $currentMonth->month)->whereYear('clock_in', $currentMonth->year)->get();

        foreach ($days as $day) {
            $dailyAttendance = $attendances->first(function ($attendance) use ($day) {
                return $attendance->clock_in->isSameDay($day);
            });

            // データがない場合は null
            $dailyAttendances[] = [
                'date' => $day,
                'clock_in' => $dailyAttendance ? $dailyAttendance->clock_in->format('H:i') : null,
                'clock_out' => $dailyAttendance && $dailyAttendance->clock_out ? $dailyAttendance->clock_out->format('H:i') : null,
            ];
        }

        $next = $currentMonth->copy()->addMonth();
        $prev = $currentMonth->copy()->subMonth();

        return view(
            'attendance.attendance_list',
            compact('title', 'currentMonth', 'next', 'prev', 'days', 'dailyAttendances')
        );
    }
}
