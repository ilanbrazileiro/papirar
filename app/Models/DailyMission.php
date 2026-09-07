<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyMission extends Model
{
    protected $fillable = ['user_id', 'mission_date', 'code', 'target', 'completed_at'];

    protected $casts = [
        'user_id' => 'integer',
        'mission_date' => 'date',
        'target' => 'integer',
        'completed_at' => 'datetime',
    ];
}
