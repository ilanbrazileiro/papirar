<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'study_session_id',
        'selected_alternative_id',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function session()
    {
        return $this->belongsTo(StudySession::class, 'study_session_id');
    }

    public function selectedAlternative()
    {
        return $this->belongsTo(Alternative::class, 'selected_alternative_id');
    }
}