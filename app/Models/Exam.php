<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'corporation_id',
        'title',
        'year',
        'exam_type',
        'status',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'year' => 'integer',
    ];

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function plannedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'exam_subjects')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->orderBy('exam_subjects.sort_order')
            ->orderBy('subjects.name');
    }

    public function subjects(): BelongsToMany
    {
        return $this->plannedSubjects();
    }

    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
