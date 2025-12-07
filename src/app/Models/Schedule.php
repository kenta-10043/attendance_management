<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'schedule_start',
        'schedule_end',
        'schedule_total',
        'schedule_break',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
