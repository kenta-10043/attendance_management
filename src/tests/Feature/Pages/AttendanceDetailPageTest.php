<?php

namespace Tests\Feature\Pages;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function attendance_detail_display_date(): void
    {
        $user = User::factory()->create();
        $status = Status::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 10:00:00',
            'date' => '2025-10-01',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSee('2025年');
        $response->assertSee('10月1日');
    }

    /** @test */
    public function attendance_detail_display_name(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
        $status = Status::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 10:00:00',
            'date' => '2025-10-01',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSee('テストユーザー');
    }

    /** @test */
    public function attendance_detail_display_work_time(): void
    {
        $clockInDateTime = '2025-10-01 09:00:00';
        $clockOutDateTime = '2025-10-01 10:00:00';
        $user = User::factory()->create();
        $status = Status::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => $clockInDateTime,
            'clock_out' => $clockOutDateTime,
            'date' => '2025-10-01',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSee('2025年');
        $response->assertSee('10月1日');
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /** @test */
    public function attendance_detail_display_break_time(): void
    {
        $clockInDateTime = '2025-10-01 09:00:00';
        $clockOutDateTime = '2025-10-01 17:00:00';
        $startBreakDateTime = '2025-10-01 12:00:00';
        $endBreakDateTime = '2025-10-01 13:00:00';

        $user = User::factory()->create();
        $status = Status::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => $clockInDateTime,
            'clock_out' => $clockOutDateTime,
            'date' => '2025-10-01',
        ]);
        $breakTime = BreakTime::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_break' => $startBreakDateTime,
            'end_break' => $endBreakDateTime,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSee('2025年');
        $response->assertSee('10月1日');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
