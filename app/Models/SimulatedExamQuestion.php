<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulatedExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulated_exam_id',
        'question_id',
        'position',
        'selected_alternative_id',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
        'position' => 'integer',
    ];

    public function simulatedExam(): BelongsTo
    {
        return $this->belongsTo(SimulatedExam::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedAlternative(): BelongsTo
    {
        return $this->belongsTo(Alternative::class, 'selected_alternative_id');
    }
}
