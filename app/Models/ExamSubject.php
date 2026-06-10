<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubject extends Model
{
    use HasFactory;

    protected $table = 'exam_subjects';

    protected $fillable = [
        'exam_id',
        'subject_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function examSubjectTopics(): HasMany
    {
        return $this->hasMany(ExamSubjectTopic::class)
            ->orderBy('sort_order');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'exam_subject_topics')
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->orderBy('exam_subject_topics.sort_order')
            ->orderBy('topics.name');
    }
}
