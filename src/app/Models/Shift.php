<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_pattern',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function schedule()
    {
        return $this->hasOne(Schedule::class);
    }
}
