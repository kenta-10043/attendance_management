<?php

namespace App\Calendars;

use Carbon\Carbon;

class CalendarView
{
    protected $carbon;

    public function __construct(?Carbon $date = null)
    {
        $this->carbon = $date ? $date->copy()->startOfMonth() : now()->startOfMonth();
    }

    public function getDate(): Carbon  // このカレンダーの基準月
    {
        return $this->carbon->copy();
    }

    public function getTitle()  // カレンダーのタイトル作成
    {
        return $this->carbon->format('Y/m');
    }

    public function getMonth()
    {
        $startOfMonth = $this->carbon->copy()->startOfMonth();
        $endOfMonth = $this->carbon->copy()->endOfMonth();

        $days = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $days[] = $current->copy();
            $current->addDay();
        }

        return $days;
    }
}
