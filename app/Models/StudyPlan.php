<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyPlan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'weekdays', 'subject_ids', 'daily_question_target', 'starts_on', 'is_active'];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'weekdays' => 'array',
        'subject_ids' => 'array',
        'daily_question_target' => 'integer',
        'starts_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
