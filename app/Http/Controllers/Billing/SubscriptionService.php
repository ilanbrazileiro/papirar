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
            ->where('status', 'active')
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
        return DB::transaction(function () use ($user, $plan) {
            return Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'starts_at' => null,
                'expires_at' => null,
                'canceled_at' => null,
            ]);
        });
    }

    public function createPendingTransaction(User $user, Subscription $subscription, string $gateway, array $payload = []): PaymentTransaction
    {
        $amount = (float) $subscription->plan->price;

        return PaymentTransaction::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'gateway' => $gateway,
            'external_id' => Arr::get($payload, 'external_id'),
            'amount' => $amount,
            'status' => 'pending',
            'payload' => ! empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'paid_at' => null,
        ]);
    }

    public function markTransactionAsPending(PaymentTransaction $transaction, array $payload = []): PaymentTransaction
    {
        $transaction->update([
            'status' => 'pending',
            'external_id' => Arr::get($payload, 'external_id', $transaction->external_id),
            'payload' => ! empty($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $transaction->payload,
        ]);

        return $transaction->fresh();
    }

    public function activateSubscriptionFromTransaction(PaymentTransaction $transaction, ?array $payload = null): Subscription
    {
        return DB::transaction(function () use ($transaction, $payload) {
            $transaction->loadMissing('subscription.plan');

            $subscription = $transaction->subscription;

            if (! $subscription || ! $subscription->plan) {
                throw new ModelNotFoundException('Assinatura ou plano não encontrado para a transação informada.');
            }

            $startsAt = now();
            $currentActive = Subscription::query()
                ->where('user_id', $subscription->user_id)
                ->where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '>=', now())
                ->latest('expires_at')
                ->first();

            if ($currentActive) {
                $startsAt = Carbon::parse($currentActive->expires_at);
                $currentActive->update([
                    'status' => 'expired',
                ]);
            }

            $expiresAt = (clone $startsAt)->addDays((int) $subscription->plan->duration_days);

            $subscription->update([
                'status' => 'active',
                'starts_at' => $subscription->starts_at ?? now(),
                'expires_at' => $expiresAt,
                'canceled_at' => null,
            ]);

            $transaction->update([
                'status' => 'paid',
                'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $transaction->payload,
                'paid_at' => now(),
            ]);

            return $subscription->fresh(['plan', 'transactions']);
        });
    }

    public function failTransaction(PaymentTransaction $transaction, ?array $payload = null): PaymentTransaction
    {
        $transaction->update([
            'status' => 'failed',
            'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $transaction->payload,
        ]);

        if ($transaction->subscription && $transaction->subscription->status === 'pending') {
            $transaction->subscription->update([
                'status' => 'failed',
            ]);
        }

        return $transaction->fresh();
    }

    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'canceled',
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

        $subscription = $this->createPendingSubscription($user, $plan);
        $transaction = $this->createPendingTransaction($user, $subscription, 'mercado_pago');

        $checkoutData = $mercadoPagoService->createCheckoutPreference($user, $subscription, $transaction);

        $transaction->update([
            'external_id' => $checkoutData['external_id'] ?? $transaction->external_id,
            'payload' => json_encode($checkoutData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return [
            'plan' => $plan,
            'subscription' => $subscription->fresh('plan'),
            'transaction' => $transaction->fresh(),
            'checkout' => $checkoutData,
        ];
    }
}
