<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alternative extends Model
{
    protected $fillable = [
        'question_id',
        'letter',
        'text',
        'is_correct',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}