<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStatus;
use App\Http\Requests\ApplicationRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function storeApplication(ApplicationRequest $request, $id)
    {
        $validated = $request->validated();

        $attendance = Attendance::findOrFail($id);

        $application = $attendance->application()->create([
            'user_id' => $attendance->user_id,
            'new_clock_in' => Carbon::parse("{$attendance->date} {$validated['new_clock_in']}")->format('Y-m-d H:i:s'),
            'new_clock_out' => Carbon::parse("{$attendance->date} {$validated['new_clock_out']}")->format('Y-m-d H:i:s'),
            'notes' => $validated['notes'],
            'approval' => ApprovalStatus::PENDING->value, // 未承認
            'applied_at' => $request->input('applied_at', now()),
        ]);

        $starts = $validated['new_start_break'] ?? [];
        $ends = $validated['new_end_break'] ?? [];

        foreach ($starts as $i => $start) {
            $end = $ends[$i] ?? null;

            if ($start && $end) {
                $application->breakTimes()->create([
                    'user_id' => $attendance->user_id,
                    'attendance_id' => $attendance->id,
                    'start_break' => Carbon::parse("{$attendance->date} {$start}")->format('Y-m-d H:i:s'),
                    'end_break' => Carbon::parse("{$attendance->date} {$end}")->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->back()->with('success', '修正申請を登録しました');
    }

    public function applicationList(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        $pendingAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->whereHas('application', function ($query) {
                $query->where('approval', 1);
            })
            ->with('application')
            ->get();

        $approvedAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->whereHas('application', function ($query) {
                $query->where('approval', 2);
            })
            ->with('application')
            ->get();

        return view('attendance.stamp_correction_request_list', compact('pendingAttendances', 'approvedAttendances'));
    }
}
