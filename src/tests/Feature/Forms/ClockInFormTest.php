<?php

namespace Tests\Feature\Forms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Status;

class ClockInFormTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $statusWorking;
    protected $statusFinished;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザー作成
        $this->user = User::factory()->create();

        // ステータス作成
        $this->statusWorking = Status::create(['status' => 1]);   // 出勤中
        $this->statusFinished = Status::create(['status' => 3]);  // 退勤済
    }

    /** @test */
    public function attendance_clock_in_display_status_working()
    {
        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('<button class="button__attendance" type="submit">出勤</button>', false);
        $response->assertSee('勤務外');

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_in' => now(),
            'clock_out' => null,
            'date' => now()->toDateString(),
            'status_id' => $this->statusWorking->id,
            'type' => 'start'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertDontSee('勤務外');
    }

    /** @test */
    public function attendance_clock_in_cannot_display_work_button_after_finish()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        // 退勤済みの勤怠作成
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'date' => now()->toDateString(),
            'status_id' => $this->statusFinished->id,
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertDontSee('<button class="button__attendance" type="submit">出勤</button>', false);
        $response->assertDontSee('出勤中');
    }

    /** @test */
    public function attendance_clock_in_is_displayed_in_list()
    {
        $response = $this->actingAs($this->user)->get('/attendance');

        $specifiedDate = now()->toDateString();
        $clockInDateTime = now()->toDateTimeString();

        $formData = [
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_in' => $clockInDateTime,
            'clock_out' => null,
            'date' => $specifiedDate,
            'status_id' => $this->statusWorking->id,
            'type' => 'start'
        ];

        $response = $this->post(route('attendance.store'), $formData);

        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($specifiedDate)->isoFormat('MM/DD(ddd)'));
        $response->assertSee(Carbon::parse($clockInDateTime)->format('H:i'));
    }
}
