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
        'import_type',
        'corporation_id',
        'exam_id',
        'exam_board_id',
        'source_material_id',
        'exam_year',
        'exam_reference',
        'source_type',
        'filename',
        'original_filename',
        'source_file_path',
        'answer_file_path',
        'answer_original_filename',
        'ai_model',
        'ai_attempts',
        'processing_error',
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
        'corporation_id' => 'integer',
        'exam_id' => 'integer',
        'exam_board_id' => 'integer',
        'source_material_id' => 'integer',
        'exam_year' => 'integer',
        'ai_attempts' => 'integer',
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

    public function attempts(): HasMany
    {
        return $this->hasMany(QuestionImportAiAttempt::class, 'batch_id');
    }

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examBoard(): BelongsTo
    {
        return $this->belongsTo(ExamBoard::class, 'exam_board_id');
    }

    public function problematicRows(): HasMany
    {
        return $this->rows()->whereIn('status', ['error', 'duplicate', 'ignored']);
    }
}
