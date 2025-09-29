<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_id',
        'date',
        'clock_in',
        'clock_out',
        'notes',
        'approval',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function breakTime()
    {
        return $this->hasMany('App\Models\BreakTime');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status')->withDefault(
            [
                'status' => 0,
            ]
        );
    }

    public function isOffDuty(): bool
    {
        return $this->status->status === 0;
    }

    public function isWorking(): bool
    {
        return $this->status->status === 1;
    }

    public function isOnBreak(): bool
    {
        return $this->status->status === 2;
    }

    public function isFinished(): bool
    {
        return $this->status->status === 3;
    }
}
