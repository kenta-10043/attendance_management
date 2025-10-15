<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;


class AttendancePageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }


    /** @test */
    public function attendance_display_now_date()
    {

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);

        $today = now()->isoFormat('YYYY年MM月DD日 (ddd)');
        $response->assertSee($today);
    }

    /** @test */
    public function attendance_display_status_off_duty()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }


    /** @test */
    public function attendance_display_status_working()
    {
        $status = Status::create([
            'status' => 1,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now(),
            'clock_out' => null, // 退勤していない
            'date' => now()->toDateString(),
            'status_id' => $status->id,
        ]);


        $response = $this->actingAs($this->user)
            ->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
    }

    /** @test */
    public function attendance_display_status_break()
    {
        $status = Status::create([
            'status' => 2,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
            'date' => now()->toDateString(),
            'status_id' => $status->id,
        ]);

        $breakTime = BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => now(),
            'end_break' => null,
        ]);


        $response = $this->actingAs($this->user)
            ->get('/attendance');


        $response->assertStatus(200);


        $response->assertSee('休憩中');
    }

    /** @test */
    public function attendance_display_status_finished()
    {
        $status = Status::create([
            'status' => 3,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'date' => now()->toDateString(),
            'status_id' => $status->id,
        ]);


        $response = $this->actingAs($this->user)
            ->get('/attendance');


        $response->assertStatus(200);


        $response->assertSee('退勤済');
    }
}
