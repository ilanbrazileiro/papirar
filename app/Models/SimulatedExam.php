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
        'user_id', 'title', 'corporation_id', 'exam_id', 'subject_id', 'topic_id', 'difficulty', 'source_type',
        'total_questions', 'correct_answers', 'accuracy', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'accuracy' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function corporation(): BelongsTo { return $this->belongsTo(Corporation::class); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }
    public function items(): HasMany { return $this->hasMany(SimulatedExamQuestion::class)->orderBy('position'); }
    public function questions(): HasMany { return $this->items(); }
}
