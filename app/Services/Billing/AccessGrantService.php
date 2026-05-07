<?php

namespace App\Services\Billing;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccessGrantService
{
    public function grantTrial(User $user, int $days = 7): Subscription
    {
        return $this->grantAccess(
            user: $user,
            days: $days,
            plan: $this->trialPlan(),
            cancelCurrent: false
        );
    }

    public function grantManualAccess(User $user, int $days, ?int $planId = null, bool $cancelCurrent = false): Subscription
    {
        $plan = $planId
            ? SubscriptionPlan::query()->whereKey($planId)->firstOrFail()
            : $this->manualPlan();

        return $this->grantAccess(
            user: $user,
            days: $days,
            plan: $plan,
            cancelCurrent: $cancelCurrent
        );
    }

    private function grantAccess(User $user, int $days, SubscriptionPlan $plan, bool $cancelCurrent = false): Subscription
    {
        if ($days < 1 || $days > 365) {
            throw new InvalidArgumentException('Informe uma quantidade de dias entre 1 e 365.');
        }

        return DB::transaction(function () use ($user, $days, $plan, $cancelCurrent) {
            if ($cancelCurrent) {
                Subscription::query()
                    ->where('user_id', $user->id)
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->update([
                        'status' => Subscription::STATUS_CANCELED,
                        'canceled_at' => now(),
                    ]);
            }

            $currentActive = Subscription::query()
                ->where('user_id', $user->id)
                ->where('status', Subscription::STATUS_ACTIVE)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                })
                ->orderByDesc('expires_at')
                ->lockForUpdate()
                ->first();

            $startsAt = now();

            // Se o cliente já tem acesso ativo e o admin não cancelou o período atual,
            // somamos os dias ao vencimento atual para não prejudicar quem já pagou.
            $baseDate = $currentActive && !$cancelCurrent && $currentActive->expires_at instanceof Carbon
                ? $currentActive->expires_at->copy()
                : $startsAt->copy();

            if ($baseDate->lt($startsAt)) {
                $baseDate = $startsAt->copy();
            }

            return Subscription::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'expires_at' => $baseDate->addDays($days),
                'canceled_at' => null,
            ]);
        });
    }

    private function trialPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::query()->firstOrCreate(
            ['slug' => 'teste-7-dias'],
            [
                'name' => 'Teste grátis de 7 dias',
                'description' => 'Acesso automático liberado após cadastro.',
                'price' => 0,
                'duration_days' => 7,
                'active' => true,
            ]
        );
    }

    private function manualPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::query()->firstOrCreate(
            ['slug' => 'liberacao-manual'],
            [
                'name' => 'Liberação manual',
                'description' => 'Acesso concedido manualmente pelo administrador.',
                'price' => 0,
                'duration_days' => 1,
                'active' => true,
            ]
        );
    }
}
