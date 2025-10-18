<?php

namespace Tests\Feature\Admin\Forms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\application;
use Illuminate\Database\Eloquent\Factories\Sequence;
use App\Enums\ApprovalStatus;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $startDate;
    protected $startClockIn;
    protected $startClockOut;
    protected $user;
    protected $status;


    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();

        $this->status = Status::factory()->create(['id' => 1]);

        $this->startDate = Carbon::parse('2025-10-01');
        $this->startClockIn = Carbon::parse('2025-10-01 09:00:00');
        $this->startClockOut = Carbon::parse('2025-10-01 18:00:00');

        $this->user = User::factory()
            ->has(
                Attendance::factory()
                    ->state(
                        [
                            'date' => $this->startDate->copy()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->toDateTimeString(),
                            'status_id' => $this->status->id,
                        ],
                    )
            )->create(['name' => 'テストユーザー']);

        foreach ($this->user->attendances as $attendance) {
            BreakTime::factory()
                ->count(2)
                ->state(new Sequence(
                    [
                        'user_id' => $attendance->user_id,
                        'start_break' => "{$attendance->date} 12:00:00",
                        'end_break'   => "{$attendance->date} 13:00:00",
                    ],
                    [
                        'user_id' => $attendance->user_id,
                        'start_break' => "{$attendance->date} 15:00:00",
                        'end_break'   => "{$attendance->date} 15:15:00",
                    ]
                ))
                ->for($attendance)
                ->create();
        }
    }

    /** @test */
    public function admin_attendance_display_detail(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $attendance = $this->user->attendances->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $attendance->id]));

        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSeeInOrder([
            'テストユーザー',
            '2025年',
            '10月1日',
            '9:00',
            '18:00',
            '12:00',
            '13:00',
            '15:00',
            '15:15',

        ]);
        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_detail_form_new_clock_in_before(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $attendance = $this->user->attendances->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $attendance->id]));

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_in'  => '20:00',
            'clock_out' => '17:10',
            'notes'        => 'テスト',
            'approval'     => ApprovalStatus::PENDING->value,
            'applied_at'   => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.admin_storeAttendance', ['id' => $attendance->id ?? null]), $formData);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_detail_form_new_start_break_in_before(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $attendance = $this->user->attendances->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $attendance->id]));

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_in'  => '09:10',
            'clock_out' => '17:10',
            'notes'        => 'テスト',
            'start_break' => ['18:10'],
            'end_break' => ['17:00'],
            'approval'     => ApprovalStatus::PENDING->value, // 未承認
            'applied_at'   => now(),
        ]);

        $response = $this->followingRedirects()
            ->actingAs($this->adminUser)->post(route('admin.admin_storeAttendance', ['id' => $attendance->id ?? null]), $formData);
        $response->assertSee('休憩時間が不適切な値です');
        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_detail_form_new_end_break_in_after(): void
    {

        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $attendance = $this->user->attendances->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $attendance->id]));

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $this->user->id,
            'clock_in'  => '09:10',
            'clock_out' => '17:10',
            'notes'        => 'テスト',
            'start_break' => ['16:10'],
            'end_break' => ['17:30'],
            'approval'     => ApprovalStatus::PENDING->value, // 未承認
            'applied_at'   => now(),
        ]);

        $response = $this->followingRedirects()
            ->actingAs($this->adminUser)->post(route('admin.admin_storeAttendance', ['id' => $attendance->id ?? null]), $formData);
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_detail_form_notes_required(): void
    {

        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $attendance = $this->user->attendances->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $attendance->id]));


        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'clock_in'  => '09:10',
            'clock_out' => '17:10',
            'notes'        => null,
            'start_break' => ['12:10'],
            'end_break' => ['13:30'],
            'approval'     => ApprovalStatus::PENDING->value,
            'applied_at'   => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.admin_storeAttendance', ['id' => $attendance->id ?? null]), $formData);
        $response->assertSessionHasErrors([
            'notes' => '備考を記入してください',
        ]);
        Carbon::setTestNow();
    }
}
