<?php

namespace Tests\Feature\Forms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;

class BreakFormTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $statusWorking;
    protected $statusOnBreak;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザー作成
        $this->user = User::factory()->create();

        // ステータス作成
        $this->statusWorking = Status::create(['status' => 1]);   // 出勤中
        $this->statusOnBreak = Status::create(['status' => 2]);   // 休憩中
    }

    /** @test */
    public function attendance_break_display_status_break()
    {
        // 出勤中の勤怠作成
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
            'date' => now()->toDateString(),
            'status_id' => $this->statusWorking->id,
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        // 休憩中の勤怠に変更
        $attendance->update(['status_id' => $this->statusOnBreak->id]);

        // 休憩中データ作成
        BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => now(),
            'end_break' => null,
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
        $response->assertDontSee('休憩入');
    }

    /** @test */
    public function attendance_break_display_start_and_end_break()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(5),
            'clock_out' => null,
            'date' => now()->toDateString(),
            'status_id' => $this->statusWorking->id,
        ]);

        // 出勤中表示確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        // 休憩中に更新
        $attendance->update(['status_id' => $this->statusOnBreak->id]);

        $breakTime = BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHour(4),
            'end_break' => null,
        ]);

        $attendance->update(['status_id' => $this->statusWorking->id]);
        $breakTime->update(['end_break' => now(),]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩入');
        $response->assertDontSee('休憩戻');
    }

    /** @test */
    public function attendance_break_display_end_break()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(3),
            'clock_out' => null,
            'date' => now()->toDateString(),
            'status_id' => $this->statusWorking->id,
        ]);

        // 出勤中表示確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        // 休憩中に更新
        $attendance->update(['status_id' => $this->statusOnBreak->id]);

        $breakTime = BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHour(2),
            'end_break' => null,
        ]);

        $attendance->update(['status_id' => $this->statusWorking->id]);
        $breakTime->udate(['end_break' => now()]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertDontSee('休憩戻');
    }

    /** @test */
    public function attendance_break_display_break_again()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => now()->subHours(5),
            'clock_out' => null,
            'date' => now()->toDateString(),
            'status_id' => $this->statusWorking->id,
        ]);

        // 出勤中表示確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('<span class="attendance__status">出勤中 </span>', false);
        $response->assertSee('休憩入');

        // 休憩中に更新
        $attendance->update(['status_id' => $this->statusOnBreak->id]);

        $breakTime = BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHour(4),
            'end_break' => null,
        ]);

        $attendance->update(['status_id' => $this->statusWorking->id]);
        $breakTime->udate(['end_break' => now()->subHour(3)]);

        $attendance->update(['status_id' => $this->statusOnBreak->id]);
        $breakTime = BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => now()->subHour(2),
            'end_break' => null,
        ]);


        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function attendance_break_is_displayed_in_list()
    {
        $specifiedDate = '2025-10-13';
        $clockInDateTime = Carbon::createFromFormat('Y-m-d H:i:s', "$specifiedDate 09:30:00");

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'clock_in' => $clockInDateTime->copy()->subHours(2),
            'clock_out' => null,
            'date' => $specifiedDate,
            'status_id' => $this->statusOnBreak->id,
        ]);

        $startBreak = $attendance->clock_in->copy()->addHour();
        $endBreak = $attendance->clock_in->copy()->addHours(2);

        BreakTime::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $attendance->id,
            'start_break' => $startBreak,
            'end_break' => $endBreak,
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($specifiedDate)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($startBreak->format('H:i'));
        $response->assertSee($endBreak->format('H:i'));
    }
}
