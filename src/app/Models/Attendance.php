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
                'status' => '勤務外'
            ]
        );
    }
}
