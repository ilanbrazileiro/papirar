<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class PaymentTransaction extends Model
{
    use HasFactory;

    public const GATEWAY_MERCADO_PAGO = 'mercado_pago';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'gateway',
        'external_id',
        'provider_payment_id',
        'gateway_reference',
        'amount',
        'status',
        'status_detail',
        'payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function checkoutUrl(): ?string
    {
        if ($this->status !== self::STATUS_PENDING) {
            return null;
        }

        return Arr::get($this->payload, 'init_point')
            ?? Arr::get($this->payload, 'sandbox_init_point')
            ?? Arr::get($this->payload, 'checkout.init_point')
            ?? Arr::get($this->payload, 'checkout.sandbox_init_point');
    }
}
