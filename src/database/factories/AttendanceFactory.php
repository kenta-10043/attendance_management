<?php

namespace Database\Factories;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 出勤時間を当月内でランダムに生成
        $clockIn = Carbon::instance($this->faker->dateTimeBetween($startOfMonth, $endOfMonth))
            ->setTime($this->faker->numberBetween(9, 10), $this->faker->numberBetween(0, 59));

        // 退勤時間は6〜8時間後
        $clockOut = (clone $clockIn)->addHours($this->faker->numberBetween(6, 8));

        return [
            'user_id' => 0,   // ダミー、Seederで必ず上書き
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status_id' => 3,
            'date' => $clockIn->toDateString(),
        ];
    }
}
