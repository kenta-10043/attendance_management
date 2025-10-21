<?php

namespace Tests\Feature\Forms;

use App\Enums\ApprovalStatus;
use App\Models\application;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function attendance_detail_form_new_clock_in_before(): void
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

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'new_clock_in' => '20:00',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'approval' => ApprovalStatus::PENDING->value, // 未承認
            'applied_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('attendance.storeApplication', ['id' => $attendance->id ?? null]), $formData);
        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function attendance_detail_form_new_start_break_in_before(): void
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

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'new_start_break' => ['18:10'],
            'new_end_break' => ['17:00'],
            'approval' => ApprovalStatus::PENDING->value, // 未承認
            'applied_at' => now(),
        ]);

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendance->id]), $formData);
        $response->assertSee('休憩時間が不適切な値です');
    }

    /** @test */
    public function attendance_detail_form_new_end_break_in_after(): void
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

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'new_start_break' => ['16:10'],
            'new_end_break' => ['17:30'],
            'approval' => ApprovalStatus::PENDING->value, // 未承認
            'applied_at' => now(),
        ]);

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendance->id]), $formData);
        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function attendance_detail_form_notes_required(): void
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

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => null,
            'new_start_break' => ['12:10'],
            'new_end_break' => ['13:30'],
            'approval' => ApprovalStatus::PENDING->value, // 未承認
            'applied_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendance->id]), $formData);
        $response->assertSessionHasErrors([
            'notes' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function attendance_detail_form_can_application(): void
    {
        $clockInDateTime = '2025-10-01 09:00:00';
        $clockOutDateTime = '2025-10-01 17:00:00';
        $startBreakDateTime = '2025-10-01 12:00:00';
        $endBreakDateTime = '2025-10-01 13:00:00';

        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
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

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'new_start_break' => ['12:10'],
            'new_end_break' => ['13:30'],
            'approval' => ApprovalStatus::PENDING->value,
            'applied_at' => '2025-10-20 00:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendance->id]), $formData);

        $adminUser = User::factory()->admin()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $response = $this->actingAs($adminUser)->get(route('admin.admin_application_list', ['tab' => 'pending']));

        $response->assertSee('承認待ち');
        $response->assertSee('テストユーザー');
        $response->assertSee('2025/10/01');
        $response->assertSee('テスト');
        $response->assertSee('2025/10/20');
        $response->assertSee('詳細');

        $currentMonth = Carbon::parse('2025-10-01');
        $pendingAttendance = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->whereHas('application', function ($query) {
                $query->where('approval', 1);
            })
            ->with('application')
            ->first();

        $response = $this->actingAs($adminUser)->get(route('admin.admin_approve', ['attendance_correct_request_id' => $pendingAttendance->application->id]));

        $response->assertSee('テストユーザー');
        $response->assertSee('2025年');
        $response->assertSee('10月1日');
        $response->assertSee('09:10');
        $response->assertSee('17:10');
        $response->assertSee('12:10');
        $response->assertSee('13:30');
        $response->assertSee('テスト');
        $response->assertSee('<button class="button__approval" type="submit">承認</button>', false);
    }

    /** @test */
    public function attendance_detail_form_display_application_list(): void
    {
        $clockInDateTime = '2025-10-01 09:00:00';
        $clockInDateTime2 = '2025-10-02 09:00:00';
        $clockOutDateTime = '2025-10-01 17:00:00';
        $clockOutDateTime2 = '2025-10-02 17:00:00';
        $startBreakDateTime = '2025-10-01 12:00:00';
        $startBreakDateTime2 = '2025-10-02 12:00:00';
        $endBreakDateTime = '2025-10-01 13:00:00';
        $endBreakDateTime2 = '2025-10-02 13:00:00';

        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
        $statuses = Status::factory()->createMany([
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ]);
        $attendances = Attendance::factory()->createMany([
            [
                'user_id' => $user->id,
                'status_id' => $statuses[0]->id,
                'clock_in' => $clockInDateTime,
                'clock_out' => $clockOutDateTime,
                'date' => '2025-10-01',
            ],
            [
                'user_id' => $user->id,
                'status_id' => $statuses[1]->id,
                'clock_in' => $clockInDateTime2,
                'clock_out' => $clockOutDateTime2,
                'date' => '2025-10-02',
            ],
        ]);

        $attendances[0]->breakTimes()->create([
            'user_id' => $user->id,
            'start_break' => $startBreakDateTime,
            'end_break' => $endBreakDateTime,
        ]);

        $attendances[1]->breakTimes()->create([
            'user_id' => $user->id,
            'start_break' => $startBreakDateTime2,
            'end_break' => $endBreakDateTime2,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendances[0]->id]));

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendances[0]->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'new_start_break' => ['12:10'],
            'new_end_break' => ['13:30'],
            'approval' => ApprovalStatus::PENDING->value,
            'applied_at' => '2025-10-20 00:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendances[0]->id]), $formData);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendances[1]->id]));
        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendances[1]->user_id,
            'new_clock_in' => '09:20',
            'new_clock_out' => '17:20',
            'notes' => 'テスト2',
            'new_start_break' => ['12:20'],
            'new_end_break' => ['13:40'],
            'approval' => ApprovalStatus::PENDING->value,
            'applied_at' => '2025-10-21 00:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendances[1]->id]), $formData);

        $response = $this->actingAs($user)->get(route('attendance.applicationList', ['tab' => 'pending']));

        $response->assertSeeInOrder([
            '承認待ち',
            'テストユーザー',
            '2025/10/01',
            'テスト',
            '2025/10/20',
            '詳細',

            '承認待ち',
            'テストユーザー',
            '2025/10/02',
            'テスト',
            '2025/10/21',
            '詳細',
        ]);
    }

    /** @test */
    public function attendance_detail_form_display_approval_list(): void
    {
        $clockInDateTime = '2025-10-01 09:00:00';
        $clockInDateTime2 = '2025-10-02 09:00:00';
        $clockOutDateTime = '2025-10-01 17:00:00';
        $clockOutDateTime2 = '2025-10-02 17:00:00';
        $startBreakDateTime = '2025-10-01 12:00:00';
        $startBreakDateTime2 = '2025-10-02 12:00:00';
        $endBreakDateTime = '2025-10-01 13:00:00';
        $endBreakDateTime2 = '2025-10-02 13:00:00';

        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
        $statuses = Status::factory()->createMany([
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ]);
        $attendances = Attendance::factory()->createMany([
            [
                'user_id' => $user->id,
                'status_id' => $statuses[0]->id,
                'clock_in' => $clockInDateTime,
                'clock_out' => $clockOutDateTime,
                'date' => '2025-10-01',
            ],
            [
                'user_id' => $user->id,
                'status_id' => $statuses[1]->id,
                'clock_in' => $clockInDateTime2,
                'clock_out' => $clockOutDateTime2,
                'date' => '2025-10-02',
            ],
        ]);

        $attendances[0]->breakTimes()->create([
            'user_id' => $user->id,
            'start_break' => $startBreakDateTime,
            'end_break' => $endBreakDateTime,
        ]);

        $attendances[1]->breakTimes()->create([
            'user_id' => $user->id,
            'start_break' => $startBreakDateTime2,
            'end_break' => $endBreakDateTime2,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendances[0]->id]));

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendances[0]->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'new_start_break' => ['12:10'],
            'new_end_break' => ['13:30'],
            'approval' => ApprovalStatus::PENDING->value,
            'applied_at' => '2025-10-20 00:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendances[0]->id]), $formData);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendances[1]->id]));
        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendances[1]->user_id,
            'new_clock_in' => '09:20',
            'new_clock_out' => '17:20',
            'notes' => 'テスト2',
            'new_start_break' => ['12:20'],
            'new_end_break' => ['13:40'],
            'approval' => ApprovalStatus::PENDING->value,
            'applied_at' => '2025-10-21 00:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendances[1]->id]), $formData);

        $adminUser = User::factory()->admin()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = Application::where('user_id', $user->id)->where('attendance_id', $attendances[0]->id)->first();

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendances[0]->user_id,
            'clock_in' => '09:10',
            'clock_out' => '17:10',
            'notes' => 'テスト1',
            'start_break' => ['12:10'],
            'end_break' => ['13:30'],
            'approval' => ApprovalStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($adminUser)->post(route('admin.admin_storeApprove', ['attendance_correct_request_id' => $application->id]), $formData);

        $application2 = Application::where('user_id', $user->id)->where('attendance_id', $attendances[1]->id)->first();

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendances[1]->user_id,
            'clock_in' => '09:20',
            'clock_out' => '17:20',
            'notes' => 'テスト2',
            'start_break' => ['12:20'],
            'end_break' => ['13:40'],
            'approval' => ApprovalStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($adminUser)->post(route('admin.admin_storeApprove', ['attendance_correct_request_id' => $application2->id]), $formData);

        $response = $this->actingAs($user)->get(route('attendance.applicationList', ['tab' => 'approved']));

        $response->assertSeeInOrder([
            '承認済み',
            'テストユーザー',
            '2025/10/01',
            'テスト',
            '2025/10/20',
            '詳細',

            '承認済み',
            'テストユーザー',
            '2025/10/02',
            'テスト',
            '2025/10/21',
            '詳細',
        ]);
    }

    /** @test */
    public function attendance_detail_form_application_list_show_detail(): void
    {
        $clockInDateTime = '2025-10-01 09:00:00';
        $clockOutDateTime = '2025-10-01 17:00:00';
        $startBreakDateTime = '2025-10-01 12:00:00';
        $endBreakDateTime = '2025-10-01 13:00:00';

        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
        $status = Status::factory()->create([
            'id' => 1,
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => $clockInDateTime,
            'clock_out' => $clockOutDateTime,
            'date' => '2025-10-01',
        ]);

        $attendance->breakTimes()->create([
            'user_id' => $user->id,
            'start_break' => $startBreakDateTime,
            'end_break' => $endBreakDateTime,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $formData = ([
            '_token' => csrf_token(),
            'user_id' => $attendance->user_id,
            'new_clock_in' => '09:10',
            'new_clock_out' => '17:10',
            'notes' => 'テスト',
            'new_start_break' => ['12:10'],
            'new_end_break' => ['13:30'],
            'approval' => ApprovalStatus::PENDING->value,
            'applied_at' => '2025-10-20 00:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('attendance.storeApplication', ['id' => $attendance->id]), $formData);

        $response = $this->actingAs($user)->get(route('attendance.applicationList', ['tab' => 'pending']));
        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertSeeInOrder([
            'テストユーザー',
            '2025年',
            '10月1日',
            '9:10',
            '17:10',
            '12:10',
            '13:30',
            'テスト',
        ]);
        $response->assertSee('<p class="approval-status__alert">※承認待ちのため修正はできません。</p>', false);
    }
}
