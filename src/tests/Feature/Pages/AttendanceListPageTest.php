<?php

namespace Tests\Feature\Pages;

use App\Calendars\CalendarView;
use App\Models\Attendance;
use App\Models\Status;
use App\Models\User;
use App\Services\WorkTimeCalculator;
use Carbon\Carbon;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusSeeder::class);
        $this->seed(AttendanceSeeder::class);
    }

    /** @test */
    public function attendance_list_page_display_list(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])->get();

        $calculator = new WorkTimeCalculator;

        $attendances->each(function ($attendance) use ($response, $user, $calculator) {

            $response->assertSee(Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)'));

            if ($attendance) {
                $data = $calculator->getDailyWorkAndBreak($user->id, $attendance->date);
                $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
                $response->assertSee(Carbon::parse($attendance->clock_out)->format('H:i'));

                if (! empty($break)) {
                    $break = $data['break_original'] ?? $data['break'];
                } else {
                    $response->assertSee('');
                }

                if (! empty($workTime)) {
                    $workTime = $data['work_original'] ?? $data['work'];
                    $response->assertSee($workTime);
                } else {
                    $response->assertSee('');
                }
            } else {
                $response->assertSee('');
            }
        });
    }

    /** @test */
    public function attendance_list_page_display_current_month(): void
    {
        $user = User::first();
        $date = Carbon::parse('2025-10-01');

        $calendar = new CalendarView($date);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2025/10');
    }

    /** @test */
    public function attendance_list_page_display_previous_month(): void
    {
        $user = User::first();
        $date = Carbon::parse('2025-10-01');

        $calendar = new CalendarView($date);
        $currentMonth = $calendar->getDate();
        $prev = $currentMonth->copy()->subMonth();

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.list', ['date' => $prev->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('2025/09');
    }

    /** @test */
    public function attendance_list_page_display_next_month(): void
    {
        $user = User::first();
        $date = Carbon::parse('2025-10-01');

        $calendar = new CalendarView($date);
        $currentMonth = $calendar->getDate();
        $next = $currentMonth->copy()->addMonth();

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.list', ['date' => $next->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('2025/11');
    }

    /** @test */
    public function attendance_list_page_display_detail(): void
    {
        $user = User::factory()->create();
        // $status = Status::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            // 'status_id' => 3,
            'clock_in' => '2025-10-01 09:00:00',
            'clock_out' => '2025-10-01 10:00:00',
            'date' => '2025-10-01',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('<h2 class="attendance__title">勤怠詳細</h2>', false);
        $response->assertSee('2025年');
        $response->assertSee('10月1日');
    }
}
