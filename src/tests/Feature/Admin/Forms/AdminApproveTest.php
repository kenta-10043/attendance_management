<?php

namespace Tests\Feature\Admin\Forms;

use App\Enums\ApprovalStatus;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApproveTest extends TestCase
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

    protected $application1;

    protected $application2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();

        $this->status1 = Status::factory()->create(['id' => 1]);
        $this->status2 = Status::factory()->create(['id' => 2]);
        $this->status3 = Status::factory()->create(['id' => 3]);
        $this->status4 = Status::factory()->create(['id' => 4]);

        $this->startDate = Carbon::parse('2025-10-01');
        $this->startClockIn = Carbon::parse('2025-10-01 09:00:00');
        $this->startClockOut = Carbon::parse('2025-10-01 18:00:00');

        $this->user1 = User::factory()
            ->has(
                Attendance::factory()
                    ->count(2)
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

        $firstAttendance1 = $this->user1->attendances->first();
        $this->application1 = Application::factory()
            ->has(
                BreakTime::factory()
                    ->count(2)
                    ->state(new Sequence(
                        [
                            'user_id' => $this->user1->id,
                            'attendance_id' => $firstAttendance1->id,
                            'start_break' => '2025-10-01 12:10:00',
                            'end_break' => '2025-10-01 13:10:00',
                        ],
                        [
                            'user_id' => $this->user1->id,
                            'attendance_id' => $firstAttendance1->id,
                            'start_break' => '2025-10-01 15:10:00',
                            'end_break' => '2025-10-01 15:25:00',
                        ]
                    ))
            )->create(
                [
                    'user_id' => $this->user1->id,
                    'attendance_id' => $firstAttendance1->id,
                    'new_clock_in' => $this->startClockIn->copy()->addHour()->toDateTimeString(),
                    'new_clock_out' => $this->startClockOut->copy()->addHour()->toDateTimeString(),
                    'notes' => 'テスト申請1',
                    'approval' => ApprovalStatus::PENDING->value,
                    'applied_at' => '2025-10-05 00:00:00',
                ],
            );

        $this->user2 = User::factory()
            ->has(
                Attendance::factory()
                    ->count(2)
                    ->state(new Sequence(
                        [
                            'date' => $this->startDate->copy()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour()->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour()->toDateTimeString(),
                            'status_id' => $this->status3->id,
                        ],
                        [
                            'date' => $this->startDate->copy()->addDay()->toDateString(),
                            'clock_in' => $this->startClockIn->copy()->addHour(2)->toDateTimeString(),
                            'clock_out' => $this->startClockOut->copy()->addHour(2)->toDateTimeString(),
                            'status_id' => $this->status4->id,
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

        $firstAttendance2 = $this->user2->attendances->first();
        Application::factory()->has(
            BreakTime::factory()
                ->count(2)
                ->state(new Sequence(
                    [
                        'user_id' => $this->user2->id,
                        'attendance_id' => $firstAttendance2->id,
                        'start_break' => '2025-10-01 12:10:00',
                        'end_break' => '2025-10-01 13:10:00',
                    ],
                    [
                        'user_id' => $this->user2->id,
                        'attendance_id' => $firstAttendance2->id,
                        'start_break' => '2025-10-01 15:10:00',
                        'end_break' => '2025-10-01 15:25:00',
                    ]
                ))
        )
            ->create(
                [
                    'user_id' => $this->user2->id,
                    'attendance_id' => $firstAttendance2->id,
                    'new_clock_in' => $this->startClockIn->copy()->addHour(2)->toDateTimeString(),
                    'new_clock_out' => $this->startClockOut->copy()->addHour(2)->toDateTimeString(),
                    'notes' => 'テスト申請2',
                    'approval' => ApprovalStatus::PENDING->value,
                    'applied_at' => '2025-10-06 00:00:00',
                ],
            );
    }

    /** @test */
    public function admin_approve_display_pending_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_application_list', ['tab' => 'pending']));

        $response->assertSee('<h2 class="attendance__tittle">申請一覧</h2>', false);
        $response->assertSeeInOrder([
            '承認待ち',
            'テストユーザー1',
            '2025/10/01',
            'テスト申請1',
            '2025/10/05',
        ], [
            '承認待ち',
            'テストユーザー2',
            '2025/10/01',
            'テスト申請2',
            '2025/10/06',
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_approve_display_approved_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));

        $secondAttendance1 = $this->user1->attendances->skip(1)->first();
        $secondAttendance2 = $this->user2->attendances->skip(1)->first();

        Application::factory()->createMany([
            [
                'user_id' => $this->user1->id,
                'attendance_id' => $secondAttendance1->id,
                'new_clock_in' => null,
                'new_clock_out' => null,
                'notes' => 'テスト承認済み1',
                'approval' => ApprovalStatus::APPROVED->value,
                'applied_at' => '2025-10-05 00:00:00',
            ],
            [
                'user_id' => $this->user2->id,
                'attendance_id' => $secondAttendance2->id,
                'new_clock_in' => null,
                'new_clock_out' => null,
                'notes' => 'テスト承認済み2',
                'approval' => ApprovalStatus::APPROVED->value,
                'applied_at' => '2025-10-06 00:00:00',
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_application_list', ['tab' => 'approved']));

        $response->assertSee('<h2 class="attendance__tittle">申請一覧</h2>', false);
        $response->assertSeeInOrder([
            '承認済み',
            'テストユーザー1',
            '2025/10/02',
            'テスト承認済み1',
            '2025/10/05',
        ], [
            '承認済み',
            'テストユーザー2',
            '2025/10/02',
            'テスト承認済み2',
            '2025/10/06',
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_approve_display_pending_detail(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_approve', ['attendance_correct_request_id' => $this->application1->id]));

        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSee('<button class="button__approval" type="submit">承認</button>', false);
        $response->assertSeeInOrder([
            'テストユーザー1',
            '2025年',
            '10月1日',
            '10:00',
            '19:00',
            '12:10',
            '13:10',
            '15:10',
            '15:25',
            'テスト申請1',
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_approve_display_can_approval(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-10-01 00:00:00'));
        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_approve', ['attendance_correct_request_id' => $this->application1->id]));

        $firstAttendance1 = $this->user1->attendances->first();
        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $this->user1->id,
            'attendance_id' => $firstAttendance1->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'notes' => 'テスト申請1',
            'start_break' => ['12:10', '15:10'],
            'end_break' => ['13:10', '15:25'],
            'approval' => ApprovalStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.admin_storeApprove', ['attendance_correct_request_id' => $this->application1->id]), $formData);

        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_application_list', ['tab' => 'approved']));

        $response->assertSeeInOrder(
            [
                '承認済み',
                'テストユーザー1',
                '2025/10/01',
                'テスト申請1',
                '2025/10/05',
            ]
        );

        $response = $this->actingAs($this->adminUser)->get(route('admin.admin_attendance_detail', ['id' => $firstAttendance1->id]));

        $response->assertSee('<h2 class="attendance__tittle">勤怠詳細</h2>', false);
        $response->assertSee('<button class="button__approved" type="submit" disabled>承認済み</button>', false);
        $response->assertSeeInOrder([
            'テストユーザー1',
            '2025年',
            '10月1日',
            '10:00',
            '19:00',
            '12:10',
            '13:10',
            '15:10',
            '15:25',
            'テスト申請1',
        ]);

        Carbon::setTestNow();
    }
}
