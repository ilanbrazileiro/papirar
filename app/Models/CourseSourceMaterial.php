<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSourceMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'source_material_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'course_id' => 'integer',
        'source_material_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function sourceMaterial(): BelongsTo
    {
        return $this->belongsTo(SourceMaterial::class);
    }
}
