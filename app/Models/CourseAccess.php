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

    public const TYPE_MANUAL = 'manual';
    public const TYPE_TRIAL = 'trial';
    public const TYPE_PAID = 'paid';
    public const TYPE_BONUS = 'bonus';

    protected $fillable = [
        'user_id',
        'course_id',
        'subscription_id',
        'status',
        'access_type',
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

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_EXPIRED => 'Expirado',
            self::STATUS_CANCELED => 'Cancelado',
        ];
    }

    public static function accessTypeOptions(): array
    {
        return [
            self::TYPE_MANUAL => 'Manual',
            self::TYPE_TRIAL => 'Teste grátis',
            self::TYPE_PAID => 'Pago',
            self::TYPE_BONUS => 'Bônus',
        ];
    }

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

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function accessTypeLabel(): string
    {
        return self::accessTypeOptions()[$this->access_type ?: self::TYPE_MANUAL] ?? ($this->access_type ?: self::TYPE_MANUAL);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_CANCELED => 'danger',
            default => 'secondary',
        };
    }
}
