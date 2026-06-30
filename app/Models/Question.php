<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_ARCHIVED = 'archived';

    public const STUDENT_VISIBLE_STATUSES = [
        self::STATUS_PUBLISHED,
        self::STATUS_REVIEWED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Rascunho',
        self::STATUS_PUBLISHED => 'Publicada',
        self::STATUS_REVIEWED => 'Revisada',
        self::STATUS_ARCHIVED => 'Arquivada',
    ];

    protected $fillable = [
        'corporation_id',
        'exam_id',
        'exam_board_id',
        'subject_id',
        'topic_id',
        'statement',
        'question_type',
        'difficulty',
        'source_type',
        'source_reference',
        'source_material_id',
        'commented_answer',
        'status',
        'created_by',
    ];

    protected $casts = [
        'corporation_id' => 'integer',
        'exam_id' => 'integer',
        'exam_board_id' => 'integer',
        'subject_id' => 'integer',
        'topic_id' => 'integer',
        'source_material_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function scopeVisibleToStudent(Builder $query): Builder
    {
        return $query->whereIn('status', self::STUDENT_VISIBLE_STATUSES);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examBoard(): BelongsTo
    {
        return $this->belongsTo(ExamBoard::class, 'exam_board_id');
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

    public function answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(QuestionFavorite::class);
    }

    public function videoLesson(): HasOne
    {
        return $this->hasOne(QuestionVideoLesson::class);
    }

    public function activeVideoLesson(): HasOne
    {
        return $this->hasOne(QuestionVideoLesson::class)->where('status', 'active');
    }
}
