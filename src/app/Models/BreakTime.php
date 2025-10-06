<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Application;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'application_id',
        'start_break',
        'end_break',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
