<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubjectTopic extends Model
{
    use HasFactory;

    protected $table = 'exam_subject_topics';

    protected $fillable = [
        'exam_subject_id',
        'topic_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'exam_subject_id' => 'integer',
        'topic_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class, 'exam_subject_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }
}
