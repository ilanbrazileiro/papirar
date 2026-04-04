<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'corporation_id',
        'title',
        'year',
        'exam_type',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function corporation()
    {
        return $this->belongsTo(Corporation::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}