<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedFilter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'corporation_id',
        'exam_id',
        'subject_id',
        'topic_id',
        'source_material_id',
        'difficulty',
        'source_type',
        'quantity',
        'mode',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'corporation_id' => 'integer',
        'exam_id' => 'integer',
        'subject_id' => 'integer',
        'topic_id' => 'integer',
        'source_material_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function sourceMaterial(): BelongsTo
    {
        return $this->belongsTo(SourceMaterial::class, 'source_material_id');
    }
}
