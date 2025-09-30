<?php


namespace App\Calendars;

use Carbon\Carbon;


class CalendarView
{
    protected $carbon;

    public function __construct(?Carbon $date = null)   // nullで日付がなかった場合には本日を呼ぶ
    {
        $this->carbon = $date ? $date->copy()->startOfMonth() : now()->startOfMonth();  //carbonのインスタンス生成 表示するカレンダー
    }

    // このカレンダーの基準月を返す
    public function getDate(): Carbon
    {
        return $this->carbon->copy();
    }

    public function getTitle()  //カレンダーのタイトル作成
    {
        return $this->carbon->format('Y/n');
    }

    public function getMonth()
    {
        $startOfMonth = $this->carbon->copy()->startOfMonth();
        $endOfMonth = $this->carbon->copy()->endOfMonth();

        $days = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $days[] = $current->copy();  // Carbonインスタンスを保存
            $current->addDay();
        }
        return $days;
    }
}
