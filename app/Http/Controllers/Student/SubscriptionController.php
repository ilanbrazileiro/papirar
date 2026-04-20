<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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
            $checkout = $this->subscriptionService->checkout(
                Auth::user(),
                (int) $validated['plan_id'],
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
                'plan_id' => $validated['plan_id'],
                'exception' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Não foi possível iniciar o pagamento da assinatura.');
        }
    }

    public function history(): View
    {
        $subscriptions = Subscription::query()
            ->with(['plan', 'transactions'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(15);

        return view('student.subscriptions.history', compact('subscriptions'));
    }
}
