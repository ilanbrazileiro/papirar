<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    public const SCOPE_GENERAL = 'general';
    public const SCOPE_CORPORATION_SPECIFIC = 'corporation_specific';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
        'scope',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }

    public function plannedExams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_subjects')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function isCorporationSpecific(): bool
    {
        return $this->scope === self::SCOPE_CORPORATION_SPECIFIC;
    }
}
