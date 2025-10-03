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

        foreach ($allUsers as $user) {
            $attendances = Attendance::factory()->count(20)->create(['user_id' => $user->id]);

            foreach ($attendances as $attendance) {
                $breakCount = rand(1, 2);

                for ($i = 0; $i < $breakCount; $i++) {
                    $startLimit = Carbon::parse($attendance->clock_in)->addHour();
                    $endLimit = Carbon::parse($attendance->clock_out)->subHour();

                    $start = $startLimit->copy()->addSeconds(rand(0, max(0, $endLimit->timestamp - $startLimit->timestamp)));
                    $duration = rand(30, 90);
                    $end = $start->copy()->addMinutes($duration);

                    if ($end > Carbon::parse($attendance->clock_out)) {
                        $end = Carbon::parse($attendance->clock_out)->subMinutes(5);
                    }

                    BreakTime::factory()->create([
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
