<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetentionFeedback extends Model
{
    protected $table = 'retention_feedback';

    protected $fillable = ['user_id','course_id','course_access_id','reason','notes','stage'];
    public function courseAccess(): BelongsTo { return $this->belongsTo(CourseAccess::class); }
}
