<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Application;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StatusSeeder::class);
        $this->call([
            AttendanceSeeder::class,
        ]);




        // 管理者ユーザーを1人作成
        User::factory()->admin()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
