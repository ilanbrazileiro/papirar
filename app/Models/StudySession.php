<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudySession extends Model
{
    protected $fillable = [
        'user_id',
        'corporation_id',
        'subject_id',
        'mode',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function corporation()
    {
        return $this->belongsTo(Corporation::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function sessionQuestions()
    {
        return $this->hasMany(StudySessionQuestion::class)->orderBy('position');
    }

    public function answers()
    {
        return $this->hasMany(UserAnswer::class, 'study_session_id');
    }
}