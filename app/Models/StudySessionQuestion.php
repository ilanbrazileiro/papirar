<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySessionQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_session_id',
        'question_id',
        'position',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'position' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(StudySession::class, 'study_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
