<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class WorkTimeCalculator
{
    public function getDailyWorkAndBreak(int $userId, string $date): ?array
    {
        // その日の勤怠データを1件取得
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (! $attendance) {
            return null; // 完全に勤怠データがない場合のみ null
        }

        // 出勤・退勤時刻をCarbonに変換
        $start = Carbon::parse($attendance->clock_in);
        $end = Carbon::parse($attendance->clock_out);

        // 出勤～退勤までの合計時間（分）
        $totalMinutes = $end->diffInMinutes($start);

        // 全休憩（現在の状態＝申請済みも含む）
        $breakMinutes = 0;
        $breaks = BreakTime::where('attendance_id', $attendance->id)->get();

        foreach ($breaks as $break) {
            if ($break->start_break && $break->end_break) {
                $breakMinutes += Carbon::parse($break->end_break)
                    ->diffInMinutes(Carbon::parse($break->start_break));
            }
        }

        // 申請前（application_idがNULL）の休憩合計
        $originalBreakMinutes = 0;
        $originalBreaks = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('application_id')
            ->get();

        foreach ($originalBreaks as $break) {
            if ($break->start_break && $break->end_break) {
                $originalBreakMinutes += Carbon::parse($break->end_break)
                    ->diffInMinutes(Carbon::parse($break->start_break));
            }
        }
        // 実働時間（分）
        $workMinutes = $totalMinutes - $breakMinutes;
        // 申請前実働時間（分）
        $workOriginalMinutes = $totalMinutes - $originalBreakMinutes;

        // 配列で返す
        return [
            'id' => $attendance->id,
            'user_name' => $attendance->user->name ?? '不明',
            'clock_in' => $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null,    // 出勤
            'clock_out' => $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null,   // 退勤
            'work' => $this->formatMinutes($workMinutes),     // 実働時間
            'work_original' => $this->formatMinutes($workOriginalMinutes),  // 実働時間（申請前の休憩ベース）
            'break' => $this->formatMinutes($breakMinutes),    // 休憩時間
            'break_original' => $this->formatMinutes($originalBreakMinutes), // 申請前の休憩時間
            'approval' => optional($attendance->application)->approval,
        ];
    }

    /**
     * 分数を「HH:MM」形式に変換
     *
     * 例: 90 → "01:30"
     */
    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remain = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remain);
    }
}
