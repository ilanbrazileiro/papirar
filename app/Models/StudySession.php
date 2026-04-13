<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'corporation_id', 'exam_id', 'subject_id', 'topic_id', 'mode', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function corporation(): BelongsTo { return $this->belongsTo(Corporation::class); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }
    public function sessionQuestions(): HasMany { return $this->hasMany(StudySessionQuestion::class)->orderBy('position'); }
    public function answers(): HasMany { return $this->hasMany(UserAnswer::class, 'study_session_id'); }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'study_session_questions')
            ->withPivot(['id', 'position', 'answered_at', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderBy('study_session_questions.position');
    }
}
