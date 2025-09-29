<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AttendanceStatus;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'changed_at',
    ];

    public function attendances()
    {
        return $this->hasMany('App\Models\Attendance');
    }

    public function getLabelAttribute(): string
    {
        return AttendanceStatus::from($this->status)->label();
    }
}
