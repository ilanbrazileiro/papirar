<?php

namespace App\Services\Billing;

use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CoursePaymentService
{
    public function checkout(User $user, Course $course, string $billingCycle, MercadoPagoService $mercadoPagoService): array
    {
        if (! $course->active || ! $course->is_public) {
            throw new InvalidArgumentException('Curso indisponível para assinatura.');
        }

        $amount = $course->priceForBillingCycle($billingCycle);
        $periodDays = $course->periodDaysForBillingCycle($billingCycle);

        if ($amount <= 0 || $periodDays <= 0) {
            throw new InvalidArgumentException('Valor ou período inválido para este curso.');
        }

        return DB::transaction(function () use ($user, $course, $billingCycle, $amount, $periodDays, $mercadoPagoService) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => null,
                'course_id' => $course->id,
                'status' => Subscription::STATUS_PENDING,
                'billing_cycle' => $billingCycle,
                'period_days' => $periodDays,
                'amount' => $amount,
                'starts_at' => null,
                'expires_at' => null,
                'canceled_at' => null,
            ]);

            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'course_id' => $course->id,
                'gateway' => PaymentTransaction::GATEWAY_MERCADO_PAGO,
                'external_id' => null,
                'amount' => $amount,
                'status' => PaymentTransaction::STATUS_PENDING,
                'payload' => [
                    'billing_cycle' => $billingCycle,
                    'period_days' => $periodDays,
                    'course_id' => $course->id,
                ],
                'paid_at' => null,
            ]);

            $checkoutData = $mercadoPagoService->createCourseCheckoutPreference(
                $user,
                $course,
                $subscription,
                $transaction,
                $billingCycle,
                $periodDays,
                $amount
            );

            $transaction->update([
                'external_id' => $checkoutData['external_id'] ?? $transaction->external_id,
                'payload' => array_merge($transaction->payload ?? [], $checkoutData),
            ]);

            return [
                'course' => $course,
                'subscription' => $subscription->fresh('course'),
                'transaction' => $transaction->fresh(),
                'checkout' => $checkoutData,
            ];
        });
    }

    public function activateCourseAccessFromTransaction(PaymentTransaction $transaction, ?array $payload = null): Subscription
    {
        return DB::transaction(function () use ($transaction, $payload) {
            $transaction->loadMissing(['subscription.course', 'course']);

            if ($transaction->isPaid()) {
                return $transaction->subscription->fresh(['course', 'transactions']);
            }

            $subscription = $transaction->subscription;
            $course = $subscription?->course ?: $transaction->course;

            if (! $subscription || ! $course) {
                throw new ModelNotFoundException('Assinatura ou curso não encontrado para a transação informada.');
            }

            $now = now();
            $periodDays = (int) ($subscription->period_days ?: Arr::get($transaction->payload, 'period_days', 30));
            $periodDays = max(1, $periodDays);

            $currentAccess = CourseAccess::query()
                ->where('user_id', $subscription->user_id)
                ->where('course_id', $course->id)
                ->where('status', CourseAccess::STATUS_ACTIVE)
                ->where(function ($query) use ($now) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $now);
                })
                ->latest('ends_at')
                ->first();

            $baseDate = $now->copy();

            if ($currentAccess && $currentAccess->ends_at && $currentAccess->ends_at->greaterThan($now)) {
                $baseDate = Carbon::parse($currentAccess->ends_at);
            }

            $startsAt = $subscription->starts_at ?: $now;
            $expiresAt = $baseDate->copy()->addDays($periodDays);

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

            if (! $currentAccess) {
                $currentAccess = new CourseAccess([
                    'user_id' => $subscription->user_id,
                    'course_id' => $course->id,
                    'starts_at' => $now,
                    'bonus_days' => 0,
                    'cancel_at_period_end' => false,
                ]);
            }

            $currentAccess->fill([
                'subscription_id' => $subscription->id,
                'status' => CourseAccess::STATUS_ACTIVE,
                'access_type' => CourseAccess::TYPE_PAID,
                'ends_at' => $expiresAt,
                'canceled_at' => null,
                'cancel_at_period_end' => false,
            ]);

            if (! $currentAccess->starts_at) {
                $currentAccess->starts_at = $now;
            }

            $currentAccess->save();

            return $subscription->fresh(['course', 'transactions', 'courseAccess']);
        });
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

    public function failTransaction(PaymentTransaction $transaction, ?array $payload = null): PaymentTransaction
    {
        $transaction->loadMissing('subscription');

        $transaction->update([
            'status' => PaymentTransaction::STATUS_FAILED,
            'payload' => $payload ?: $transaction->payload,
        ]);

        if ($transaction->subscription && $transaction->subscription->status === Subscription::STATUS_PENDING) {
            $transaction->subscription->update([
                'status' => Subscription::STATUS_FAILED,
                'canceled_at' => now(),
            ]);
        }

        return $transaction->fresh();
    }
}
