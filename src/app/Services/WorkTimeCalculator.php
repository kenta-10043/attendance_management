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

        // データがない or 出勤・退勤のどちらかが未記録 → 計算せず終了
        if (!$attendance || !$attendance->clock_in || !$attendance->clock_out) {
            return null;
        }

        // 出勤・退勤時刻をCarbonに変換
        $start = Carbon::parse($attendance->clock_in);
        $end   = Carbon::parse($attendance->clock_out);

        // 出勤～退勤までの合計時間（分）
        $totalMinutes = $end->diffInMinutes($start);

        // 休憩の合計（分）
        $breakMinutes = 0;
        $breaks = BreakTime::where('attendance_id', $attendance->id)->get();
        foreach ($breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $breakMinutes += Carbon::parse($break->break_end)
                    ->diffInMinutes(Carbon::parse($break->break_start));
            }
        }

        // 実働時間（分）
        $workMinutes = $totalMinutes - $breakMinutes;

        // 配列で返す
        return [
            'clock_in'  => $attendance->clock_in,                  // 出勤
            'clock_out' => $attendance->clock_out,                 // 退勤
            'work'      => $this->formatMinutes($workMinutes),     // 実働時間
            'break'     => $this->formatMinutes($breakMinutes),    // 休憩時間
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
