<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExamBoard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ExamBoard $examBoard) {
            if (blank($examBoard->slug)) {
                $examBoard->slug = Str::slug($examBoard->name);
            } else {
                $examBoard->slug = Str::slug($examBoard->slug);
            }
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'exam_board_id');
    }
}
