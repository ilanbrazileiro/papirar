<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\AccessGrantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::query()
            ->where('role', 'student')
            ->with(['subscriptions' => function ($query) {
                $query->with('plan')->latest('expires_at');
            }])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('access_status'), function ($query) use ($request) {
                match ($request->string('access_status')->toString()) {
                    'active' => $query->whereHas('subscriptions', function ($q) {
                        $q->where('status', Subscription::STATUS_ACTIVE)
                            ->where(function ($inner) {
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>=', now());
                            });
                    }),
                    'inactive' => $query->whereDoesntHave('subscriptions', function ($q) {
                        $q->where('status', Subscription::STATUS_ACTIVE)
                            ->where(function ($inner) {
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>=', now());
                            });
                    }),
                    default => null,
                };
            })
            ->when($request->filled('account_status'), function ($query) use ($request) {
                if ($request->string('account_status')->toString() === 'enabled') {
                    $query->where('is_active', 1);
                }

                if ($request->string('account_status')->toString() === 'disabled') {
                    $query->where('is_active', 0);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        $customer->load([
            'subscriptions' => fn ($query) => $query->with('plan')->latest('id'),
            'address',
            'supportTickets',
        ]);

        $plans = SubscriptionPlan::query()
            ->where('active', true)
            ->orderBy('price')
            ->orderBy('name')
            ->get();

        return view('admin.customers.show', compact('customer', 'plans'));
    }

    public function edit(User $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer->id)],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpf' => $data['cpf'] ?? null,
            'phone' => $data['phone'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $customer->update($payload);

        return redirect()->route('admin.customers.edit', $customer)->with('success', 'Cliente atualizado com sucesso.');
    }

    public function grantAccess(Request $request, User $customer, AccessGrantService $accessGrantService): RedirectResponse
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'cancel_current' => ['nullable', 'boolean'],
        ], [
            'days.required' => 'Informe a quantidade de dias para liberar.',
            'days.min' => 'A liberação mínima é de 1 dia.',
            'days.max' => 'A liberação máxima é de 365 dias por operação.',
            'plan_id.exists' => 'Plano selecionado inválido.',
        ]);

        $subscription = $accessGrantService->grantManualAccess(
            user: $customer,
            days: (int) $data['days'],
            planId: !empty($data['plan_id']) ? (int) $data['plan_id'] : null,
            cancelCurrent: (bool) ($data['cancel_current'] ?? false)
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', "Acesso liberado até {$subscription->expires_at->format('d/m/Y H:i')}.");
    }
}
