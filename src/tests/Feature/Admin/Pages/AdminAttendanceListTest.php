<?php

namespace Tests\Feature\Admin\Pages;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected $startDate;

    protected $startClockIn;

    protected $startClockOut;

    protected $user1;

    protected $user2;

    protected $status1;

    protected $status2;

    protected $status3;

    protected $status4;

    protected $status5;

    protected $status6;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();

        $this->status1 = Status::factory()->create(['id' => 1]);
        $this->status2 = Status::factory()->create(['id' => 2]);
        $this->status3 = Status::factory()->create(['id' => 3]);
        $this->status4 = Status::factory()->create(['id' => 4]);
        $this->status5 = Status::factory()->create(['id' => 5]);
        $this->status6 = Status::factory()->create(['id' => 6]);

        $this->startDate = Carbon::parse('2025-10-01');
        $this->startClockIn = Carbon::parse('2025-10-01 09:00:00');
        $this->startClockOut = Carbon::parse('2025-10-01 18:00:00');

        $this->user1 = User::factory()
            ->has(
                Attendance::factory()
                    ->count(3)
                    ->state(new Sequence(
                        [
                            'date' => $this->startDate->copy()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->toDateTimeString(),
                            'status_id' => $this->status1->id,
                        ],
                        [
                            'date' => $this->startDate->copy()->addDay()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour()->toDateTimeString(),
                            'status_id' => $this->status2->id,
                        ],
                        [
                            'date' => $this->startDate->copy()->addDays(2)->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour(2)->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour(2)->toDateTimeString(),
                            'status_id' => $this->status3->id,
                        ],
                    ))

            )
            ->create(['name' => 'テストユーザー1']);

        foreach ($this->user1->attendances as $attendance) {
            BreakTime::factory()
                ->count(2)
                ->state(new Sequence(
                    [
                        'user_id' => $attendance->user_id,
                        'start_break' => "{$attendance->date} 12:00:00",
                        'end_break' => "{$attendance->date} 13:00:00",
                    ],
                    [
                        'user_id' => $attendance->user_id,
                        'start_break' => "{$attendance->date} 15:00:00",
                        'end_break' => "{$attendance->date} 15:15:00",
                    ]
                ))
                ->for($attendance)
                ->create();
        }

        $this->user2 = User::factory()
            ->has(
                Attendance::factory()
                    ->count(3)
                    ->state(new Sequence(
                        [
                            'date' => $this->startDate->copy()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour()->toDateTimeString(),
                            'status_id' => $this->status4->id,
                        ],
                        [
                            'date' => $this->startDate->copy()->addDay()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour(2)->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour(2)->toDateTimeString(),
                            'status_id' => $this->status5->id,
                        ],
                        [
                            'date' => $this->startDate->copy()->addDays(2)->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour(3)->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour(3)->toDateTimeString(),
                            'status_id' => $this->status6->id,
                        ],
                    ))

            )
            ->create(['name' => 'テストユーザー2']);

        foreach ($this->user2->attendances as $attendance) {
            BreakTime::factory()
                ->count(2)
                ->state(new Sequence(
                    [
                        'user_id' => $attendance->user_id,
                        'start_break' => "{$attendance->date} 12:00:00",
                        'end_break' => "{$attendance->date} 13:00:00",
                    ],
                    [
                        'user_id' => $attendance->user_id,
                        'start_break' => "{$attendance->date} 15:00:00",
                        'end_break' => "{$attendance->date} 15:15:00",
                    ]
                ))
                ->for($attendance)
                ->create();
        }
    }

    /** @test */
    public function admin_attendance_display_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_list'));

        $response->assertSee('2025年10月1日の勤怠');
        $response->assertSeeInOrder(
            [
                'テストユーザー1',
                '09:00',
                '18:00',
                '1:15',
                '7:45',
                '詳細',
            ],
            [
                'テストユーザー2',
                '10:00',
                '19:00',
                '1:15',
                '7:45',
                '詳細',
            ]
        );
        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_display_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_list'));

        $response->assertSee('2025年10月1日の勤怠');

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_display_day_before(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-02 00:00:00'));
        $prev = Carbon::parse('2025-10-02 00:00:00')->subDay()->toDateString();

        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_list', ['date' => $prev]));

        $response->assertSee('2025年10月1日の勤怠');
        $response->assertSeeInOrder(
            [
                'テストユーザー1',
                '09:00',
                '18:00',
                '1:15',
                '7:45',
                '詳細',
            ],
            [
                'テストユーザー2',
                '10:00',
                '19:00',
                '1:15',
                '7:45',
                '詳細',
            ]
        );

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_attendance_display_day_after(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-09-30 00:00:00'));
        $next = Carbon::parse('2025-09-30 00:00:00')->addDay()->toDateString();

        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_list', ['date' => $next]));

        $response->assertSee('2025年10月1日の勤怠');
        $response->assertSeeInOrder(
            [
                'テストユーザー1',
                '09:00',
                '18:00',
                '1:15',
                '7:45',
                '詳細',
            ],
            [
                'テストユーザー2',
                '10:00',
                '19:00',
                '1:15',
                '7:45',
                '詳細',
            ]
        );

        Carbon::setTestNow();
    }
}
