<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LibraryDocument extends Model
{
    protected $fillable = ['course_id','type','title','year','exam_board','description','summary','source_url','file_key','file_name','file_size','preview_key','is_published'];
    protected $casts = ['is_published'=>'boolean','year'=>'integer','file_size'=>'integer'];
    public function publicSlug(): string
    {
        return \Illuminate\Support\Str::slug($this->title) ?: 'prova-oficial';
    }
    public function publicUrl(): string
    {
        return route('library.exams.show', ['document' => $this->id, 'slug' => $this->publicSlug()]);
    }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
