<?php

namespace App\Http\Controllers;

use App\Calendars\CalendarView;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Enums\AttendanceStatus;
use App\Services\WorkTimeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        $today = Carbon::now();
        $now = $today->isoFormat('YYYY年MM月DD日 (ddd)');
        $attendance = $user->attendances()->whereDate('date', $today)->first();
        $latestAttendance = $user->attendances()->latest()->first();
        if ($latestAttendance) {
            if ($latestAttendance->isFinished()) {
                // 退勤時刻から1時間以内なら「退勤済み」
                if (Carbon::parse($latestAttendance->clock_out)->gt(now()->subHour())) {
                    $statusLabel = '退勤済';
                } else {
                    $statusLabel = '勤務外';
                }
            } else {
                // 退勤していない場合は通常通り
                $statusLabel = $latestAttendance->status->label;
            }
        } else {
            $statusLabel = '勤務外';
        }

        return view('attendance.attendance', compact('now', 'today', 'statusLabel', 'attendance'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        $type = $request->input('type');
        if ($type === 'start' && ! $attendance) {

            $status = Status::firstOrCreate(['status' => AttendanceStatus::WORKING->value]);  // 1 = 出勤中

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $request->input('date', now()->toDateString()),
                'clock_in' => $request->input('clock_in', now()->toDateTimeString()),
                'status_id' => $status->id,
            ]);

            return back()->with('message', '出勤しました');
        }

        if (! $attendance) {
            return back()->with('message', '出勤していません');
        }

        if ($type === 'break_start' && $attendance->isWorking()) {
            BreakTime::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'start_break' => $request->input('start_break', now()->toDateTimeString()),
            ]);

            $status = Status::firstOrCreate(['status' => AttendanceStatus::BREAK->value]);  // 2 = 休憩中
            $attendance->update(['status_id' => $status->id]);
            $attendance->refresh();

            return back()->with('message', '休憩開始しました');
        }

        if ($type === 'break_end' && $attendance->isOnBreak()) {
            $breakTime = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('end_break')
                ->first();

            if ($breakTime) {
                $breakTime->update(['end_break' => $request->input('end_break', now()->toDateTimeString())]);
            }
            $status = Status::firstOrCreate(['status' => AttendanceStatus::WORKING->value]);
            $attendance->update(['status_id' => $status->id]);
            $attendance->refresh();

            return back()->with('message', '休憩終了しました');
        }

        if ($type === 'end' && $attendance->isWorking()) {
            $attendance->update([
                'clock_out' => $request->input('clock_out', now()->toDateTimeString()),
            ]);

            $status = Status::firstOrCreate(['status' => AttendanceStatus::FINISHED->value]); // 3 = 退勤済
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

        $calculator = new WorkTimeCalculator;

        $monthly = [];
        foreach ($days as $day) {
            $monthly[] = $calculator->getDailyWorkAndBreak($user->id, $day->format('Y-m-d'));
        }

        $next = $currentMonth->copy()->addMonth();
        $prev = $currentMonth->copy()->subMonth();

        return view(
            'attendance.attendance_list',
            compact('title', 'currentMonth', 'next', 'prev', 'days', 'monthly')
        );
    }

    public function detail($id)
    {
        $attendance = Attendance::findOrFail($id)->refresh();
        $userName = $attendance->user->name;
        $attendanceDate = Carbon::parse($attendance->date);

        $attendanceClockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i')  : null;
        $attendanceClockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null;
        $attendanceStartBreaks = $attendance->breakTimes->pluck('start_break')->map(fn($t) => $t ? Carbon::parse($t) : null);
        $attendanceEndBreaks = $attendance->breakTimes->pluck('end_break')->map(fn($t) => $t ? Carbon::parse($t) : null);

        $application = $attendance->application()->latest()->first();

        if ($application) {
            $applicationClockIn = $application->new_clock_in ? Carbon::parse($application->new_clock_in)->format('H:i') : null;
            $applicationClockOut = $application->new_clock_out ? Carbon::parse($application->new_clock_out)->format('H:i') : null;

            $applicationStartBreaks = $application->breakTimes()->pluck('start_break')->map(fn($t) => $t ? Carbon::parse($t) : null);
            $applicationEndBreaks = $application->breakTimes()->pluck('end_break')->map(fn($t) => $t ? Carbon::parse($t) : null);
        } else {
            $applicationClockIn = null;
            $applicationClockOut = null;
            $applicationStartBreaks = collect();
            $applicationEndBreaks = collect();
        }


        return view('attendance.attendance_detail', compact(
            'attendance',
            'userName',
            'attendanceDate',
            'attendanceClockIn',
            'attendanceClockOut',
            'attendanceStartBreaks',
            'attendanceEndBreaks',
            'application',
            'applicationClockIn',
            'applicationClockOut',
            'applicationStartBreaks',
            'applicationEndBreaks'
        ));
    }
}
