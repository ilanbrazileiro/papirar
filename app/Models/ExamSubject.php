<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'exam_id' => 'integer',
        'subject_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topicLinks(): HasMany
    {
        return $this->hasMany(ExamSubjectTopic::class, 'exam_subject_id');
    }

    public function sourceMaterialLinks(): HasMany
    {
        return $this->hasMany(ExamSubjectSourceMaterial::class, 'exam_subject_id');
    }
}
