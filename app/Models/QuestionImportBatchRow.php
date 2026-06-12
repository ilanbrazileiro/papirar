<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionImportBatchRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'row_number',
        'status',
        'raw_data',
        'normalized_statement',
        'error_message',
        'duplicate_question_id',
        'created_question_id',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QuestionImportBatch::class, 'batch_id');
    }

    public function createdQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'created_question_id');
    }

    public function duplicateQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'duplicate_question_id');
    }
}
