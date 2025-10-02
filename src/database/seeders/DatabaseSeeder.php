<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Database\Factories\BreakTimeFactory;

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



        // 5人の一般ユーザー作成
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            // 各ユーザーに20日の勤怠を作成
            $attendances = Attendance::factory()
                ->count(20)
                ->create(['user_id' => $user->id]);

            foreach ($attendances as $attendance) {
                // 休憩1〜2回
                $breakCount = rand(1, 2);

                for ($i = 0; $i < $breakCount; $i++) {
                    // 勤務時間の範囲（出勤1時間後〜退勤1時間前）
                    $startLimit = $attendance->clock_in->copy()->addHour();
                    $endLimit = $attendance->clock_out->copy()->subHour();

                    // 開始時間を範囲内でランダムに
                    $start = $startLimit->copy()->addSeconds(rand(0, max(0, $endLimit->timestamp - $startLimit->timestamp)));

                    // 終了時間は30〜90分後
                    $duration = rand(30, 90);
                    $end = $start->copy()->addMinutes($duration);

                    // 終了時間が退勤を超える場合は退勤時間の少し前に調整
                    if ($end > $attendance->clock_out) {
                        $end = $attendance->clock_out->copy()->subMinutes(5);
                    }

                    // BreakTime を作成
                    BreakTimeFactory::new()->create([
                        'attendance_id' => $attendance->id,
                        'user_id' => $user->id,
                        'start_break' => $start,
                        'end_break' => $end,
                    ]);
                }
            }
        }
    }
}
