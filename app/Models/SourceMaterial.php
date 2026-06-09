<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceMaterial extends Model
{
    use HasFactory;

    protected $table = 'source_materials';

    protected $fillable = [
        'corporation_id',
        'subject_id',
        'title',
        'slug',
        'description',
        'material_type',
        'year',
        'reference_code',
        'url',
        'active',
    ];

    protected $casts = [
        'corporation_id' => 'integer',
        'subject_id' => 'integer',
        'year' => 'integer',
        'active' => 'boolean',
    ];

    public function corporation()
    {
        return $this->belongsTo(Corporation::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'source_material_id');
    }

    public function examSubjectLinks()
    {
        return $this->hasMany(ExamSubjectSourceMaterial::class, 'source_material_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
