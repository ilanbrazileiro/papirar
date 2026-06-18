<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'question_id',
        'note',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'question_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
