<?php

namespace App\Services\Billing;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionService
{
    public function getActiveSubscriptionForUser(User $user): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest('expires_at')
            ->first();
    }

    public function getSubscriptionHistoryForUser(User $user)
    {
        return Subscription::query()
            ->with(['plan', 'transactions'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();
    }

    public function createPendingSubscription(User $user, SubscriptionPlan $plan): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => null,
            'expires_at' => null,
            'canceled_at' => null,
        ]);
    }

    public function createPendingTransaction(User $user, Subscription $subscription, string $gateway, array $payload = []): PaymentTransaction
    {
        return PaymentTransaction::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'gateway' => $gateway,
            'external_id' => Arr::get($payload, 'external_id'),
            'amount' => (float) $subscription->plan->price,
            'status' => PaymentTransaction::STATUS_PENDING,
            'payload' => ! empty($payload) ? $payload : null,
            'paid_at' => null,
        ]);
    }

    public function markTransactionAsPending(PaymentTransaction $transaction, array $payload = []): PaymentTransaction
    {
        $transaction->update([
            'status' => PaymentTransaction::STATUS_PENDING,
            'external_id' => Arr::get($payload, 'external_id', $transaction->external_id),
            'payload' => ! empty($payload) ? $payload : $transaction->payload,
        ]);

        return $transaction->fresh();
    }

    public function activateSubscriptionFromTransaction(PaymentTransaction $transaction, ?array $payload = null): Subscription
    {
        return DB::transaction(function () use ($transaction, $payload) {
            $transaction->loadMissing('subscription.plan');

            if ($transaction->isPaid()) {
                return $transaction->subscription->fresh(['plan', 'transactions']);
            }

            $subscription = $transaction->subscription;

            if (! $subscription || ! $subscription->plan) {
                throw new ModelNotFoundException('Assinatura ou plano não encontrado para a transação informada.');
            }

            $now = now();
            $baseDate = $now->copy();

            $currentActive = Subscription::query()
                ->where('user_id', $subscription->user_id)
                ->where('status', Subscription::STATUS_ACTIVE)
                ->whereNotNull('expires_at')
                ->where('expires_at', '>=', $now)
                ->where('id', '!=', $subscription->id)
                ->latest('expires_at')
                ->first();

            if ($currentActive && $currentActive->expires_at) {
                $baseDate = Carbon::parse($currentActive->expires_at);
            }

            $startsAt = $subscription->starts_at ?: $now;
            $expiresAt = $baseDate->copy()->addDays((int) $subscription->plan->duration_days);

            $subscription->update([
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'canceled_at' => null,
            ]);

            $transaction->update([
                'status' => PaymentTransaction::STATUS_PAID,
                'payload' => $payload ?: $transaction->payload,
                'paid_at' => $now,
            ]);

            return $subscription->fresh(['plan', 'transactions']);
        });
    }

    public function failTransaction(PaymentTransaction $transaction, ?array $payload = null): PaymentTransaction
    {
        $transaction->update([
            'status' => PaymentTransaction::STATUS_FAILED,
            'payload' => $payload ?: $transaction->payload,
        ]);

        if ($transaction->subscription && $transaction->subscription->status === Subscription::STATUS_PENDING) {
            $transaction->subscription->update([
                'status' => Subscription::STATUS_FAILED,
            ]);
        }

        return $transaction->fresh();
    }

    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);

        return $subscription->fresh();
    }

    public function checkout(User $user, int|string $planId, MercadoPagoService $mercadoPagoService): array
    {
        $plan = SubscriptionPlan::query()
            ->where('active', true)
            ->find($planId);

        if (! $plan) {
            throw new InvalidArgumentException('Plano inválido ou inativo.');
        }

        return DB::transaction(function () use ($user, $plan, $mercadoPagoService) {
            $subscription = $this->createPendingSubscription($user, $plan);
            $transaction = $this->createPendingTransaction($user, $subscription, PaymentTransaction::GATEWAY_MERCADO_PAGO);

            $checkoutData = $mercadoPagoService->createCheckoutPreference($user, $subscription, $transaction);

            $transaction->update([
                'external_id' => $checkoutData['external_id'] ?? $transaction->external_id,
                'payload' => $checkoutData,
            ]);

            return [
                'plan' => $plan,
                'subscription' => $subscription->fresh('plan'),
                'transaction' => $transaction->fresh(),
                'checkout' => $checkoutData,
            ];
        });
    }
}
