<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'corporation_id',
        'exam_id',
        'subject_id',
        'topic_id',
        'statement',
        'question_type',
        'difficulty',
        'source_type',
        'source_reference',
        'commented_answer',
        'status',
        'created_by',
    ];

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function alternatives(): HasMany
    {
        return $this->hasMany(Alternative::class)->orderBy('letter');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(QuestionComment::class);
    }

    public function difficultyVotes(): HasMany
    {
        return $this->hasMany(QuestionDifficultyVote::class);
    }
}
