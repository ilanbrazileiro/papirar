<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'corporation_id',
        'exam_id',
        'subject_id',
        'topic_id',
        'statement',
        'question_type',
        'difficulty',
        'source_type',
        'source_reference',
        'commented_answer',
        'status',
        'created_by',
    ];

    public function corporation()
    {
        return $this->belongsTo(Corporation::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function alternatives()
    {
        return $this->hasMany(Alternative::class)->orderBy('letter');
    }
}