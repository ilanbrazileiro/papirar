<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseBundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_course_id',
        'included_course_id',
    ];

    protected $casts = [
        'bundle_course_id' => 'integer',
        'included_course_id' => 'integer',
    ];

    public function bundleCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'bundle_course_id');
    }

    public function includedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'included_course_id');
    }
}
