<?php

namespace App\Enums;

enum ApprovalStatus: int
{
    case NOT_APPLIED = 0;
    case PENDING = 1;
    case APPROVED = 2;


    public function label(): string
    {
        return match ($this) {
            self::NOT_APPLIED => '未申請',
            self::PENDING => '未承認',
            self::APPROVED => '承認済み',
        };
    }
}
