<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudySessionQuestion extends Model
{
    protected $fillable = [
        'study_session_id',
        'question_id',
        'position',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(StudySession::class, 'study_session_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}