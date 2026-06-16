<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAccess extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'user_id',
        'course_id',
        'subscription_id',
        'status',
        'starts_at',
        'ends_at',
        'canceled_at',
        'cancel_at_period_end',
        'bonus_days',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'subscription_id' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'bonus_days' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        return is_null($this->ends_at) || $this->ends_at->greaterThanOrEqualTo(now());
    }
}
