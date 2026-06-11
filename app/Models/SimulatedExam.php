<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimulatedExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'corporation_id',
        'exam_id',
        'subject_id',
        'topic_id',
        'source_material_id',
        'difficulty',
        'source_type',
        'total_questions',
        'correct_answers',
        'accuracy',
        'duration_minutes',
        'started_at',
        'ends_at',
        'finished_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'corporation_id' => 'integer',
        'exam_id' => 'integer',
        'subject_id' => 'integer',
        'topic_id' => 'integer',
        'source_material_id' => 'integer',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'accuracy' => 'decimal:2',
        'duration_minutes' => 'integer',
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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

    public function sourceMaterial(): BelongsTo
    {
        return $this->belongsTo(SourceMaterial::class, 'source_material_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SimulatedExamQuestion::class)->orderBy('position');
    }

    public function questions(): HasMany
    {
        return $this->items();
    }

    public function isFinished(): bool
    {
        return !is_null($this->finished_at);
    }

    public function isExpired(): bool
    {
        return !$this->isFinished()
            && !is_null($this->ends_at)
            && now()->greaterThanOrEqualTo($this->ends_at);
    }
}
