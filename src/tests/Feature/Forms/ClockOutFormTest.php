<?php

namespace Tests\Feature\Forms;

use App\Models\Attendance;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutFormTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $statusWorking;

    protected $statusFinished;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->statusWorking = Status::create(['status' => 1]);   // 出勤中
        $this->statusFinished = Status::create(['status' => 3]);  // 退勤済
    }

    /** @test */
    public function attendance_clock_out_display_status_finished()
    {
        $specifiedDate = now()->toDateString();
        $clockInDateTime = now()->toDateTimeString();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => $specifiedDate,
            'clock_out' => null,
            'date' => $clockInDateTime,
            'status_id' => $this->statusWorking->id,
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');
        $response->assertSee('退勤');

        $response = $this->actingAs($this->user)->get('/attendance');

        $clockOutDateTime = Carbon::parse($clockInDateTime)->addHours(7);
        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_out' => $clockOutDateTime->toDateTimeString(),
            'status_id' => $this->statusFinished->id,
            'type' => 'end',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSee('退勤済');
        $response->assertDontSee('<button class="button__attendance" type="submit">出勤</button>', false);
        $response->assertDontSee('<span class="attendance__status">出勤中 </span>', false);
    }

    /** @test */
    public function attendance_clock_out_is_displayed_in_list()
    {
        $response = $this->actingAs($this->user)->get('/attendance');

        $specifiedDate = now()->toDateString();
        $clockInDateTime = now()->toDateTimeString();
        $clockOutDateTime = Carbon::parse($clockInDateTime)->addHours(7);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_in' => $clockInDateTime,
            'clock_out' => null,
            'date' => $specifiedDate,
            'status_id' => $this->statusWorking->id,
            'type' => 'start',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_out' => $clockOutDateTime->toDateTimeString(),
            'status_id' => $this->statusFinished->id,
            'type' => 'end',
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($clockInDateTime)->isoFormat('MM/DD(ddd)'));
        $response->assertSee(Carbon::parse($clockInDateTime)->format('H:i'));
        $response->assertSee(Carbon::parse($clockOutDateTime)->format('H:i'));
    }
}
