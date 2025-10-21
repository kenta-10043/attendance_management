<?php

namespace Tests\Feature\Forms;

use App\Models\Attendance;
use App\Models\Status;
use App\Models\User;
use App\Services\WorkTimeCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakFormTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $attendance;

    protected $statusWorking;

    protected $statusOnBreak;

    protected $specifiedDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->statusWorking = Status::create(['status' => 1]);   // 出勤中
        $this->statusOnBreak = Status::create(['status' => 2]);   // 休憩中

        $this->specifiedDate = now()->toDateString();
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
            'date' => $this->specifiedDate,
            'status_id' => $this->statusWorking->id,
        ]);
    }

    /** @test */
    public function attendance_break_display_status_break()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'start_break' => now()->toDateString(),
            'end_break' => null,
            'status_id' => $this->statusOnBreak->id,
            'type' => 'break_start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
        $response->assertDontSee('休憩入');
    }

    /** @test */
    public function attendance_break_display_start_and_end_break()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'start_break' => now()->toDateString(),
            'end_break' => null,
            'status_id' => $this->statusOnBreak->id,
            'type' => 'break_start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $startBreakDateTime = now()->toDateTimeString();
        $endBreakDateTime = Carbon::parse($startBreakDateTime)->addHour(1);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'end_break' => $endBreakDateTime->toDateTimeString(),
            'status_id' => $this->statusWorking->id,
            'type' => 'break_end',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩入');
        $response->assertDontSee('休憩戻');
    }

    /** @test */
    public function attendance_break_display_end_break()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        $startBreakDateTime = now()->toDateTimeString();
        $endBreakDateTime = Carbon::parse($startBreakDateTime)->addHour(1);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'start_break' => $startBreakDateTime,
            'end_break' => null,
            'status_id' => $this->statusOnBreak->id,
            'type' => 'break_start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'end_break' => $endBreakDateTime->toDateTimeString(),
            'status_id' => $this->statusWorking->id,
            'type' => 'break_end',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertDontSee('休憩戻');
    }

    /** @test */
    public function attendance_break_display_break_again()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        $startBreakDateTime = now()->toDateTimeString();
        $endBreakDateTime = Carbon::parse($startBreakDateTime)->addHour(1);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'start_break' => $startBreakDateTime,
            'end_break' => null,
            'status_id' => $this->statusOnBreak->id,
            'type' => 'break_start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'end_break' => $endBreakDateTime->toDateTimeString(),
            'status_id' => $this->statusWorking->id,
            'type' => 'break_end',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $secondStartBreakDateTime = Carbon::parse($endBreakDateTime)->addHours(2);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'start_break' => $secondStartBreakDateTime->toDateString(),
            'end_break' => null,
            'status_id' => $this->statusOnBreak->id,
            'type' => 'break_start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function attendance_break_is_displayed_in_list()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        $startBreakDateTime = now()->toDateTimeString();
        $endBreakDateTime = Carbon::parse($startBreakDateTime)->addHour(1);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'start_break' => $startBreakDateTime,
            'end_break' => null,
            'status_id' => $this->statusOnBreak->id,
            'type' => 'break_start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'end_break' => $endBreakDateTime->toDateTimeString(),
            'status_id' => $this->statusWorking->id,
            'type' => 'break_end',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $calculator = new WorkTimeCalculator;
        $data = $calculator->getDailyWorkAndBreak($this->user->id, $this->specifiedDate);
        $break = $data['break_original'] ?? $data['break'];

        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($this->specifiedDate)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($break);
    }
}
