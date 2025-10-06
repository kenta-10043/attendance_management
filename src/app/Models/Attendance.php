<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\user;
use App\Models\BreakTime;
use App\Models\Status;
use App\Models\Application;




class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_id',
        'date',
        'clock_in',
        'clock_out',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function application()
    {
        return $this->hasOne(Application::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class)->withDefault(
            [
                'status' => 0,
            ]
        );
    }

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

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
