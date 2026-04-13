<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'ddd',
        'phone',
        'birth_date',
        'role',
        'is_active',
        'force_logout_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'force_logout_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function address(): HasOne { return $this->hasOne(Address::class); }
    public function studySessions(): HasMany { return $this->hasMany(StudySession::class); }
    public function answers(): HasMany { return $this->hasMany(UserAnswer::class); }
    public function savedFilter(): HasOne { return $this->hasOne(SavedFilter::class); }
    public function simulatedExams(): HasMany { return $this->hasMany(SimulatedExam::class); }
    public function questionComments(): HasMany { return $this->hasMany(QuestionComment::class); }
    public function difficultyVotes(): HasMany { return $this->hasMany(QuestionDifficultyVote::class); }
    public function subscriptions(): HasMany { return $this->hasMany(Subscription::class); }
    public function paymentTransactions(): HasMany { return $this->hasMany(PaymentTransaction::class); }
    public function currentSession(): HasOne { return $this->hasOne(UserSession::class); }
    public function supportTickets(): HasMany { return $this->hasMany(SupportTicket::class); }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'finance', 'marketing', 'content'], true);
    }
}
