<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function studySessions()
    {
        return $this->hasMany(StudySession::class);
    }

    public function answers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
