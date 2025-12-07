<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //シフトパターン設定
        DB::table('shifts')->insert([
            ['id' => 1, 'shift_pattern' => 'A'],
            ['id' => 2, 'shift_pattern' => 'AP'],
            ['id' => 3, 'shift_pattern' => 'B'],
            ['id' => 4, 'shift_pattern' => 'C'],
            ['id' => 5, 'shift_pattern' => 'CP'],
            ['id' => 6, 'shift_pattern' => '公休'],
        ]);

        //シフト予定時間設定
        DB::table('schedules')->insert([
            [
                'id' => 1,
                'shift_id' => 1,
                'schedule_start' => Carbon::parse('09:00'),
                'schedule_end' => Carbon::parse('18:00'),
                'schedule_total' => 480,
                'schedule_break' => 60,
            ],
            [
                'id' => 2,
                'shift_id' => 2,
                'schedule_start' => Carbon::parse('09:00'),
                'schedule_end' => Carbon::parse('14:00'),
                'schedule_total' => 300,
                'schedule_break' => 0,
            ],
            [
                'id' => 3,
                'shift_id' => 3,
                'schedule_start' => Carbon::parse('10:30'),
                'schedule_end' => Carbon::parse('20:00'),
                'schedule_total' => 480,
                'schedule_break' => 90,
            ],
            [
                'id' => 4,
                'shift_id' => 4,
                'schedule_start' => Carbon::parse('11:30'),
                'schedule_end' => Carbon::parse('20:30'),
                'schedule_total' => 480,
                'schedule_break' => 60,
            ],
            [
                'id' => 5,
                'shift_id' => 5,
                'schedule_start' => Carbon::parse('15:30'),
                'schedule_end' => Carbon::parse('20:30'),
                'schedule_total' => 300,
                'schedule_break' => 0,
            ],
            [
                'id' => 6,
                'shift_id' => 6,
                'schedule_start' => null,
                'schedule_end' => null,
                'schedule_total' => 0,
                'schedule_break' => 0,
            ],
        ]);
    }
}
