<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function studySessions()
    {
        return $this->hasMany(StudySession::class);
    }
}