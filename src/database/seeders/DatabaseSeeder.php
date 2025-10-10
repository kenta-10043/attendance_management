<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StatusSeeder::class);

        // 管理者ユーザーを1人作成
        User::factory()->admin()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);



        // 一般ユーザー1人
        $generalUser = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $users = User::factory()->count(5)->create(); // ランダムユーザー5人
        $allUsers = $users->push($generalUser); // 1人＋5人

        $months = [
            Carbon::now()->subMonth(),  // 先月
            Carbon::now(),              // 今月
            Carbon::now()->addMonth(),  // 来月
        ];

        foreach ($allUsers as $user) {
            foreach ($months as $month) {
                $startOfMonth = $month->copy()->startOfMonth();
                $endOfMonth   = $month->copy()->endOfMonth();

                for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                    // ランダムで休み（30%の確率で休みにする）
                    if (rand(1, 100) <= 30) {
                        continue; // この日は出勤なし
                    }

                    // 出勤時間（例：8〜9時台）
                    $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                    // 退勤時間（8〜9時間後）
                    $clockOut = $clockIn->copy()->addHours(rand(8, 9))->addMinutes(rand(0, 59));

                    // 勤怠1件作成
                    $attendance = Attendance::factory()->create([
                        'user_id'   => $user->id,
                        'date'      => $date->toDateString(),
                        'clock_in'  => $clockIn,
                        'clock_out' => $clockOut,
                    ]);

                    // --- 休憩作成（最大2回） ---
                    // 昼休憩（必須, 12:00〜13:00）
                    $lunchStart = $date->copy()->setTime(12, 0);
                    $lunchEnd   = $lunchStart->copy()->addHour();

                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'user_id'       => $user->id,
                        'start_break'   => $lunchStart,
                        'end_break'     => $lunchEnd,
                    ]);

                    // 午後の小休憩（50%の確率で追加）
                    if (rand(0, 1)) {
                        $smallBreakStart = $lunchEnd->copy()->addHours(rand(2, 3));
                        $smallBreakEnd   = $smallBreakStart->copy()->addMinutes(rand(15, 20));

                        if ($smallBreakEnd->lt($clockOut->copy()->subHours(1))) {
                            BreakTime::factory()->create([
                                'attendance_id' => $attendance->id,
                                'user_id'       => $user->id,
                                'start_break'   => $smallBreakStart,
                                'end_break'     => $smallBreakEnd,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
