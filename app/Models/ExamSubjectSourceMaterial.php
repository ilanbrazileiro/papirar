<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubjectSourceMaterial extends Model
{
    use HasFactory;

    protected $table = 'exam_subject_source_materials';

    protected $fillable = [
        'exam_subject_id',
        'source_material_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'exam_subject_id' => 'integer',
        'source_material_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class, 'exam_subject_id');
    }

    public function sourceMaterial()
    {
        return $this->belongsTo(SourceMaterial::class, 'source_material_id');
    }
}
