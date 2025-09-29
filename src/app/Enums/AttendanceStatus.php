<?php

namespace App\Enums;

enum AttendanceStatus: int
{
    case OFF_DUTY = 0;
    case WORKING = 1;
    case BREAK = 2;
    case FINISHED = 3;

    public function label(): string
    {
        return match ($this) {
            self::OFF_DUTY => '出勤外',
            self::WORKING => '勤務中',
            self::BREAK => '休憩中',
            self::FINISHED => '退勤済',
        };
    }
}
