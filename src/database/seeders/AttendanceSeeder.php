<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. ユーザー作成
        $generalUser = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $users = User::factory()->count(5)->create();
        $allUsers = $users->push($generalUser);

        // 2. 対象月（先月・今月・来月）
        $months = [
            Carbon::now()->subMonth(),  // 先月
            Carbon::now(),              // 今月
            Carbon::now()->addMonth(),  // 来月
        ];

        foreach ($allUsers as $user) {
            $userAttendances = collect();

            foreach ($months as $month) {
                $startOfMonth = $month->copy()->startOfMonth();
                $endOfMonth = $month->copy()->endOfMonth();

                for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                    if (rand(1, 100) <= 30) {
                        continue;
                    } // 30%休み

                    // 勤怠作成
                    $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                    $clockOut = $clockIn->copy()->addHours(rand(8, 9))->addMinutes(rand(0, 59));

                    $attendance = Attendance::factory()->create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                    ]);

                    // 昼休憩
                    $lunchStart = $date->copy()->setTime(12, 0);
                    $lunchEnd = $lunchStart->copy()->addHour();
                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'user_id' => $user->id,
                        'start_break' => $lunchStart,
                        'end_break' => $lunchEnd,
                    ]);

                    // 午後小休憩50%の確率
                    if (rand(0, 1)) {
                        $smallBreakStart = $lunchEnd->copy()->addHours(rand(2, 3));
                        $smallBreakEnd = $smallBreakStart->copy()->addMinutes(rand(15, 20));

                        if ($smallBreakEnd->lt($clockOut->copy()->subHours(1))) {
                            BreakTime::factory()->create([
                                'attendance_id' => $attendance->id,
                                'user_id' => $user->id,
                                'start_break' => $smallBreakStart,
                                'end_break' => $smallBreakEnd,
                            ]);
                        }
                    }

                    $userAttendances->push($attendance);
                }
            }

            // --- 今月の勤怠だけ抽出して必ず5件修正申請 ---
            $attendanceForApplication = $userAttendances
                ->filter(fn ($attendance) => Carbon::parse($attendance->date)->month === Carbon::now()->month)
                ->shuffle()
                ->take(5);

            foreach ($attendanceForApplication as $attendance) {
                $application = Application::factory()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'approval' => 1,
                    'new_clock_in' => $attendance->clock_in->copy()->addMinutes(rand(1, 5)),
                    'new_clock_out' => $attendance->clock_out->copy()->addMinutes(rand(-5, 0)),
                    'notes' => 'テスト修正申請',
                    'applied_at' => now(),
                ]);

                // BreakTimeをコピーして application_id をセット
                $attendance->breakTimes->each(function ($break) use ($application, $user) {
                    BreakTime::factory()->create([
                        'attendance_id' => $break->attendance_id,
                        'user_id' => $user->id,
                        'start_break' => Carbon::parse($break->start_break)->copy()->addMinutes(rand(-5, 5)),
                        'end_break' => Carbon::parse($break->end_break)->copy()->addMinutes(rand(-5, 5)),
                        'application_id' => $application->id,
                    ]);
                });
            }
        }
    }
}
