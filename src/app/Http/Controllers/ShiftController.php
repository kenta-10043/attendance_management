<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Calendars\CalendarView;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\User;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now();

        $calendar = new CalendarView($date);
        $title = $calendar->getTitle();
        $currentMonth = $calendar->getDate();
        $days = $calendar->getMonth();

        $next = $currentMonth->copy()->addMonth();
        $prev = $currentMonth->copy()->subMonth();

        $userSchedules = [];

        foreach ($users as $user) {

            $scheduleDates = [];             //各ユーザーのシフトを入れる箱を作る
            foreach ($days as $day) {
                $scheduleDates[$day->toDateString()] = $user->shifts()->wherePivot('schedule_date', $day)->get();  //中間テーブルから各日のシフトレコードを取得
            }
            $userSchedules[$user->id] = $scheduleDates;  //そのレコードをユーザー毎にためた箱をさらに大きな箱にためていく
        }


        return view('shifts.shift', compact('users', 'date', 'title', 'days', 'next', 'prev', 'userSchedules'));
    }
}
