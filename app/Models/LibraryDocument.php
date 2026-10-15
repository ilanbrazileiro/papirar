<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LibraryDocument extends Model
{
    protected $fillable = ['course_id','type','title','year','exam_board','description','summary','source_url','file_key','file_name','file_size','preview_key','is_published'];
    protected $casts = ['is_published'=>'boolean','year'=>'integer','file_size'=>'integer'];
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
