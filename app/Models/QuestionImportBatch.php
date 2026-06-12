<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'status',
        'total_rows',
        'valid_rows',
        'imported_rows',
        'draft_rows',
        'duplicate_rows',
        'error_rows',
        'ignored_rows',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(QuestionImportBatchRow::class, 'batch_id');
    }

    public function problematicRows(): HasMany
    {
        return $this->rows()->whereIn('status', ['error', 'duplicate', 'ignored']);
    }
}
