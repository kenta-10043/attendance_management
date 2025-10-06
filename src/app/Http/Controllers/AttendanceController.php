<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\Status;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use App\Enums\AttendanceStatus;
use App\Enums\ApprovalStatus;
use App\Calendars\CalendarView;
use App\Http\Requests\AttendanceRequest;

use App\Services\WorkTimeCalculator;


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
        $attendance = Attendance::findOrFail($id);
        $userName = $attendance->user->name;
        $attendanceDate = Carbon::parse($attendance->date);

        // 勤怠の出退勤と休憩
        $attendanceClockIn = Carbon::parse($attendance->clock_in);
        $attendanceClockOut = Carbon::parse($attendance->clock_out);
        $attendanceStartBreaks = $attendance->breakTimes->pluck('start_break')->map(fn($t) => $t ? Carbon::parse($t) : null);
        $attendanceEndBreaks = $attendance->breakTimes->pluck('end_break')->map(fn($t) => $t ? Carbon::parse($t) : null);

        // 最新の申請（存在しなければ null）
        $application = $attendance->application()->latest()->first();

        if ($application) {
            // 申請の出退勤
            $applicationClockIn  = Carbon::parse($application->new_clock_in);
            $applicationClockOut = Carbon::parse($application->new_clock_out);

            // 申請の休憩時間
            $applicationStartBreaks = $application->breakTimes()->pluck('start_break')->map(fn($t) => $t ? Carbon::parse($t) : null);
            $applicationEndBreaks   = $application->breakTimes()->pluck('end_break')->map(fn($t) => $t ? Carbon::parse($t) : null);
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




    // public function updateOrCreate(ApplicationRequest $request, $id)
    // {
    //     $validated = $request->validate();
    //     $attendance = Attendance::updateOrCreate(
    //         [$id],
    //         [
    //             'user_id' => auth()->id(),
    //             'date' => now()->toDateString(),
    //             'clock_in' => $validated['clock_in'],
    //             'clock_out' => $validated['clock_out'],
    //             'notes' => $validated['notes'],
    //             'approve' => $request->approve(),
    //         ]
    //     );

    //     $attendance->breakTimes()->delete();
    //     if (!empty($validated['start_break'])) {
    //         foreach ($validated['start_break'] as $i => $start) {
    //             $end = $validated['end_break'][$i] ?? null;

    //             if ($start && $end) {
    //                 $attendance->breakTimes()->create([
    //                     'user_id' => auth()->id(),
    //                     'start_break' => $start,
    //                     'end_break' => $end,
    //                 ]);
    //             }
    //         }
    //     }
    //     return redirect()->route('attendance.detail', $attendance->id)
    //         ->with('success', '勤怠情報を保存しました。');

}
