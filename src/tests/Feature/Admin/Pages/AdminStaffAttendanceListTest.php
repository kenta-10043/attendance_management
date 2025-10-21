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

class AdminStaffAttendanceListTest extends TestCase
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

    protected $status7;

    protected $status8;

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
        $this->status7 = Status::factory()->create(['id' => 7]);
        $this->status8 = Status::factory()->create(['id' => 8]);

        $this->startDate = Carbon::parse('2025-10-01');
        $this->startClockIn = Carbon::parse('2025-10-01 09:00:00');
        $this->startClockOut = Carbon::parse('2025-10-01 18:00:00');

        $this->user1 = User::factory()
            ->has(
                Attendance::factory()
                    ->count(5)
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
                        [
                            'date' => $this->startDate->copy()->addMonth()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->toDateTimeString(),
                            'status_id' => $this->status7->id,
                        ],
                        [
                            'date' => $this->startDate->copy()->subMonth()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->toDateTimeString(),
                            'status_id' => $this->status8->id,
                        ],

                    ))

            )
            ->create([
                'name' => 'テストユーザー1',
                'email' => 'test1@example.com',
            ]);

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
            ->create([
                'name' => 'テストユーザー2',
                'email' => 'test2@example.com',
            ]);

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
    public function admin_display_staff_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_staff_list'));
        $response->assertSeeInOrder(
            ['テストユーザー1', 'test1@example.com'],
            ['テストユーザー2', 'test2example.com']
        );
        $response->assertSee('<h2 class="attendance__tittle">スタッフ一覧</h2>', false);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_display_staff_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_index', ['id' => $this->user1->id]));

        $response->assertSee('テストユーザー1さんの勤怠');
        $response->assertSee('2025/10');
        $response->assertSeeInOrder([
            '10/01(水)',
            '9:00',
            '18:00',
            '01:15',
            '07:45',
            '詳細',
        ], [
            '10/02(木)',
            '10:00',
            '19:00',
            '01:15',
            '07:45',
            '詳細',
        ], [
            '10/03(金)',
            '11:00',
            '20:00',
            '01:15',
            '07:45',
            '詳細',
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_display_staff_attendance_list_previous_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_index', ['id' => $this->user1->id]));

        $prev = Carbon::parse('2025-10-01')->subMonth()->toDateString();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_index', ['id' => $this->user1->id, 'date' => Carbon::parse($prev)->format('Y-m')]));

        $response->assertSee('テストユーザー1さんの勤怠');
        $response->assertSee('2025/09');
        $response->assertSeeInOrder([
            '09/01(月)',
            '9:00',
            '18:00',
            '01:15',
            '07:45',
            '詳細',
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_display_staff_attendance_list_next_month(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_index', ['id' => $this->user1->id]));

        $next = Carbon::parse('2025-10-01')->addMonth()->toDateString();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_index', ['id' => $this->user1->id, 'date' => Carbon::parse($next)->format('Y-m')]));

        $response->assertSee('テストユーザー1さんの勤怠');
        $response->assertSee('2025/11');
        $response->assertSeeInOrder([
            '11/01(土)',
            '9:00',
            '18:00',
            '01:15',
            '07:45',
            '詳細',
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_display_staff_attendance_detail(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $attendance = $this->user1->attendances->first();
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $attendance->id]));

        $response->assertSeeInOrder([
            'テストユーザー1',
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
}
