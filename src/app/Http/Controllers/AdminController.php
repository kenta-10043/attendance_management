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
use App\Services\WorkTimeCalculator;
use App\Calendars\CalendarView;
use App\Enums\ApprovalStatus;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }

    public function adminAttendanceList(Request $request)
    {
        $authUser = Auth::user();

        $date = $request->input('date') ? Carbon::parse($request->input('date'))->toDateString() : now()->toDateString();

        $attendances = Attendance::whereDate('date', $date)->with('user', 'breakTimes')->get();

        $prev = Carbon::parse($date)->subDay()->toDateString();
        $next = Carbon::parse($date)->addDay()->toDateString();



        $calculator = new WorkTimeCalculator;
        $userAttendances = [];
        $users = User::where('is_admin', 0)->get();

        foreach ($users as $targetUser) {
            $data = $calculator->getDailyWorkAndBreak($targetUser->id, $date);

            if ($data) {
                $id = $data['id'];
            } else {
                // 勤怠未登録の日付にも遷移用のダミーIDを生成
                $id = 'new_' . $targetUser->id . '_' . $date;
            }

            $userAttendances[] = (object) [
                'id' => $id,
                'user_name' => $targetUser->name,
                'clock_in' => $data['clock_in'] ?? null,
                'clock_out' => $data['clock_out'] ?? null,
                'break' => $data['break'] ?? null,
                'break_original' => $data['break_original'] ?? null,
                'work' => $data['work'] ?? null,
                'work_original' => $data['work_original'] ?? null,
                'approval' => $data['approval'] ?? null,
            ];
        }


        return view('admin.admin_attendance_list', compact('authUser', 'attendances', 'date', 'userAttendances', 'prev', 'next'));
    }

    public function adminAttendanceDetail($id)
    {
        if (str_starts_with($id, 'new_')) {
            // 例: new_3_2025-10-09 → userId=3, date=2025-10-09
            [$dummy, $userId, $date] = explode('_', $id);

            // 新規用に空のAttendanceを作って渡す
            $attendance = new Attendance([
                'user_id' => $userId,
                'date' => $date,
                'clock_in' => null,
                'clock_out' => null,
            ]);

            $userName = User::find($userId)?->name ?? '不明';
            $attendanceDate = Carbon::parse($date);
            $attendanceClockIn = null;
            $attendanceClockOut = null;
            $attendanceStartBreaks = collect();
            $attendanceEndBreaks = collect();
            $application = null;
            $applicationClockIn = null;
            $applicationClockOut = null;
            $applicationStartBreaks = collect();
            $applicationEndBreaks = collect();
        } else {

            $attendance = Attendance::findOrFail($id);
            $userName = $attendance->user->name;
            $attendanceDate = Carbon::parse($attendance->date);

            // 勤怠の出退勤と休憩
            $attendanceClockIn = $attendance->clock_in !== null ? Carbon::parse($attendance->clock_in) : null;
            $attendanceClockOut = $attendance->clock_out !== null ? Carbon::parse($attendance->clock_out) : null;

            $attendanceStartBreaks = $attendance->breakTimes
                ->pluck('start_break')
                ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);
            $attendanceEndBreaks = $attendance->breakTimes
                ->pluck('end_break')
                ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);

            // 最新の申請（存在しなければ null）
            $application = $attendance->application()->latest()->first();

            if ($application) {
                $applicationClockIn  = $application->new_clock_in !== null ? Carbon::parse($application->new_clock_in) : null;
                $applicationClockOut = $application->new_clock_out !== null ? Carbon::parse($application->new_clock_out) : null;

                $applicationStartBreaks = $application->breakTimes()
                    ->pluck('start_break')
                    ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);
                $applicationEndBreaks = $application->breakTimes()
                    ->pluck('end_break')
                    ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);
            } else {
                $applicationClockIn = null;
                $applicationClockOut = null;
                $applicationStartBreaks = collect();
                $applicationEndBreaks = collect();
            }
        }


        return view('admin.admin_attendance_detail', compact(
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

    public function adminStaffList()
    {
        $users = User::where('is_admin', 0)->get();


        return view('admin.admin_staff_list', compact('users'));
    }

    public function adminAttendanceIndex(Request $request, $id)
    {

        $user = User::findOrFail($id);
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
            'admin.admin_attendance_staff',
            compact('user', 'title', 'currentMonth', 'next', 'prev', 'days', 'monthly')
        );
    }



    public function adminStoreAttendance(AttendanceRequest $request, $id)
    {
        $validated = $request->validated();

        // $id がダミーIDの場合（例: new_3_2025-10-09）か既存IDかで処理を分ける
        if (str_starts_with($id, 'new_')) {
            [$dummy, $userId, $date] = explode('_', $id);
            $attendance = null;
        } else {
            $attendance = Attendance::find($id);
            $userId = $attendance->user_id;
            $date   = $attendance->date;
        }

        DB::transaction(function () use ($attendance, $validated, $userId, $date) {

            // 1️⃣ 勤怠本体を作成 or 更新
            if ($attendance) {
                $attendance->update([
                    'clock_in'  => Carbon::parse("{$date} {$validated['clock_in']}")->format('Y-m-d H:i:s'),
                    'clock_out' => Carbon::parse("{$date} {$validated['clock_out']}")->format('Y-m-d H:i:s'),
                ]);
            } else {
                $attendance = Attendance::create([
                    'user_id'  => $userId,
                    'date'     => $date,
                    'clock_in'  => Carbon::parse("{$date} {$validated['clock_in']}")->format('Y-m-d H:i:s'),
                    'clock_out' => Carbon::parse("{$date} {$validated['clock_out']}")->format('Y-m-d H:i:s'),
                ]);
            }

            // 2️⃣ 既存休憩を削除して上書き
            $attendance->breakTimes()->delete();

            $starts = $validated['start_break'] ?? [];
            $ends   = $validated['end_break'] ?? [];

            foreach ($starts as $i => $start) {
                $end = $ends[$i] ?? null;
                if ($start && $end) {
                    $attendance->breakTimes()->create([
                        'user_id'       => $userId,
                        'attendance_id' => $attendance->id,
                        'start_break'   => Carbon::parse("{$date} {$start}")->format('Y-m-d H:i:s'),
                        'end_break'     => Carbon::parse("{$date} {$end}")->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            // 3️⃣ applications に承認済みレコードを作成（管理者修正の履歴用）
            $attendance->application()->create([
                'user_id'    => $userId,
                'approval'   => ApprovalStatus::APPROVED->value,
                'notes'    => $validated['notes'],
                'applied_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', '勤怠情報を管理者として登録・修正しました');
    }

    public function AdminApplicationList(Request $request)
    {
        $users = User::where('is_admin', 0)->get();


        // 表示する月を指定（未指定なら今月）
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        // 当月の勤怠データを取得
        $pendingAttendances = Attendance::with('application', 'user')
            ->whereHas('user', fn($q) => $q->where('is_admin', 0))
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->whereHas('application', fn($q) => $q->where('approval', 1))
            ->get();


        $approvedAttendances = Attendance::with('application', 'user')
            ->whereHas('user', fn($q) => $q->where('is_admin', 0))
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->whereHas('application', fn($q) => $q->where('approval', 2))
            ->get();


        return view(
            'admin.admin_stamp_correction_request_list',
            compact('pendingAttendances', 'approvedAttendances')
        );
    }

    public function adminApprove($attendanceCorrectRequestId)
    {
        $application = Application::findOrFail($attendanceCorrectRequestId);
        $attendance = $application->attendance;
        $userName = $attendance->user->name;
        $attendanceDate = Carbon::parse($attendance->date);

        // 勤怠の出退勤と休憩
        $attendanceClockIn = $attendance->clock_in !== null ? Carbon::parse($attendance->clock_in) : null;
        $attendanceClockOut = $attendance->clock_out !== null ? Carbon::parse($attendance->clock_out) : null;

        $attendanceStartBreaks = $attendance->breakTimes
            ->pluck('start_break')
            ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);
        $attendanceEndBreaks = $attendance->breakTimes
            ->pluck('end_break')
            ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);


        if ($application) {
            $applicationClockIn  = $application->new_clock_in !== null ? Carbon::parse($application->new_clock_in) : null;
            $applicationClockOut = $application->new_clock_out !== null ? Carbon::parse($application->new_clock_out) : null;

            $applicationStartBreaks = $application->breakTimes()
                ->pluck('start_break')
                ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);
            $applicationEndBreaks = $application->breakTimes()
                ->pluck('end_break')
                ->map(fn($t) => $t !== null ? Carbon::parse($t) : null);
        } else {
            $applicationClockIn = null;
            $applicationClockOut = null;
            $applicationStartBreaks = collect();
            $applicationEndBreaks = collect();
        }



        return view('admin.stamp_correction_request_approve', compact(
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

    public function storeAdminApprove(Request $request, $attendanceCorrectRequestId)
    {
        // 対象の申請
        $application = Application::findOrFail($attendanceCorrectRequestId);
        $attendance = $application->attendance;

        // 勤怠更新
        $attendance->update([
            'clock_in'  => Carbon::parse("{$attendance->date} {$request['clock_in']}")->format('Y-m-d H:i:s'),
            'clock_out' => Carbon::parse("{$attendance->date} {$request['clock_out']}")->format('Y-m-d H:i:s'),
        ]);

        // Application 更新
        $application->update([
            'user_id'      => $attendance->user_id,
            'new_clock_in' => null,
            'new_clock_out' => null,
            'approval'     => ApprovalStatus::APPROVED->value,
            'applied_at'   => $request->input('applied_at', now()),
        ]);

        // 休憩時間登録
        $attendance->breakTimes()->delete();
        $starts = $request['start_break'] ?? [];
        $ends   = $request['end_break'] ?? [];

        foreach ($starts as $i => $start) {
            $end = $ends[$i] ?? null;

            if ($start && $end) {
                $application->breakTimes()->create([
                    'user_id'      => $attendance->user_id,
                    'attendance_id' => $attendance->id,
                    'start_break'  => Carbon::parse("{$attendance->date} {$start}")->format('Y-m-d H:i:s'),
                    'end_break'    => Carbon::parse("{$attendance->date} {$end}")->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->back()->with('success', '修正申請を承認しました');
    }
}
