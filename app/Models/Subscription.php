<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'plan_id',
        'course_id',
        'billing_cycle',
        'period_days',
        'amount',
        'status',
        'starts_at',
        'expires_at',
        'canceled_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'canceled_at' => 'datetime',
        'period_days' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function courseAccess(): HasOne
    {
        return $this->hasOne(CourseAccess::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        return is_null($this->expires_at) || $this->expires_at->greaterThanOrEqualTo(now());
    }

    public function isCourseSubscription(): bool
    {
        return ! is_null($this->course_id);
    }
}
