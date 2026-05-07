<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Billing\MercadoPagoService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected MercadoPagoService $mercadoPagoService,
    ) {
    }

    public function index(Request $request): View
    {
        $user = Auth::user();

        $plans = SubscriptionPlan::query()
            ->where('active', true)
            ->where('is_public', true)
            ->orderBy('price')
            ->get();

        $currentSubscription = $this->subscriptionService->getActiveSubscriptionForUser($user)
            ?? Subscription::query()->with('plan')->where('user_id', $user->id)->latest('id')->first();

        $paymentStatus = $request->query('payment');

        return view('student.subscriptions.index', compact('plans', 'currentSubscription', 'paymentStatus'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
        ]);

        try {
            $plan = SubscriptionPlan::query()
                ->where('active', true)
                ->where('is_public', true)
                ->findOrFail((int) $validated['plan_id']);

            $checkout = $this->subscriptionService->checkout(
                Auth::user(),
                $plan->id,
                $this->mercadoPagoService
            );

            $checkoutUrl = $checkout['checkout']['init_point'] ?? $checkout['checkout']['sandbox_init_point'] ?? null;

            if (! $checkoutUrl) {
                return back()->with('error', 'Não foi possível obter a URL de pagamento.');
            }

            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            Log::error('Erro ao iniciar checkout da assinatura.', [
                'user_id' => Auth::id(),
                'plan_id' => $validated['plan_id'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Não foi possível iniciar o pagamento da assinatura.');
        }
    }

    public function history(): View
    {
        $subscriptions = Subscription::query()
            ->with(['plan', 'transactions' => function ($query) {
                $query->latest('id');
            }])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(15);

        return view('student.subscriptions.history', compact('subscriptions'));
    }

    public function retry(Subscription $subscription): RedirectResponse
    {
        $user = Auth::user();

        abort_unless((int) $subscription->user_id === (int) $user->id, 403);

        $subscription->load(['plan', 'transactions' => function ($query) {
            $query->latest('id');
        }]);

        if ($subscription->isActive()) {
            return redirect()
                ->route('student.subscriptions.history')
                ->with('info', 'Essa assinatura já está ativa.');
        }

        if (! $subscription->plan || ! $subscription->plan->active || ! $subscription->plan->is_public) {
            return redirect()
                ->route('student.subscriptions.index')
                ->with('error', 'Este plano não está mais disponível para pagamento.');
        }

        try {
            $pendingTransaction = $subscription->transactions
                ->first(fn (PaymentTransaction $transaction) => $transaction->status === PaymentTransaction::STATUS_PENDING);

            $existingCheckoutUrl = $pendingTransaction?->checkoutUrl();

            if ($existingCheckoutUrl) {
                return redirect()->away($existingCheckoutUrl);
            }

            $targetSubscription = $subscription;

            if ($subscription->status !== Subscription::STATUS_PENDING) {
                $targetSubscription = $this->subscriptionService->createPendingSubscription($user, $subscription->plan);
            }

            $checkout = $this->subscriptionService->createCheckoutForSubscription(
                $user,
                $targetSubscription,
                $this->mercadoPagoService
            );

            $checkoutUrl = $checkout['checkout']['init_point'] ?? $checkout['checkout']['sandbox_init_point'] ?? null;

            if (! $checkoutUrl) {
                return redirect()
                    ->route('student.subscriptions.history')
                    ->with('error', 'Não foi possível gerar uma nova tentativa de pagamento.');
            }

            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            Log::error('Erro ao retomar/tentar novamente pagamento de assinatura.', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()
                ->route('student.subscriptions.history')
                ->with('error', 'Não foi possível retomar o pagamento agora.');
        }
    }
}
