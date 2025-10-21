<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'changed_at',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getLabelAttribute(): string
    {
        return AttendanceStatus::from($this->status)->label();
    }
}
