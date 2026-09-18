<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionImportAiAttempt extends Model
{
    protected $fillable = [
        'batch_id', 'model', 'status', 'http_status', 'error_code',
        'error_message', 'usage', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'usage' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QuestionImportBatch::class, 'batch_id');
    }
}
