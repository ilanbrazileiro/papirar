<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $items = SubscriptionPlan::query()
            ->orderByDesc('active')
            ->orderByDesc('is_public')
            ->orderBy('price')
            ->paginate(20);

        return view('admin.plans.index', compact('items'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'unique:subscription_plans,slug'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active');
        $data['is_public'] = $request->boolean('is_public');

        SubscriptionPlan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plano criado.');
    }

    public function show(SubscriptionPlan $plan)
    {
        return view('admin.plans.show', compact('plan'));
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'unique:subscription_plans,slug,' . $plan->id],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active');
        $data['is_public'] = $request->boolean('is_public');

        $plan->update($data);

        return redirect()->route('admin.plans.edit', $plan)->with('success', 'Plano atualizado.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plano removido.');
    }
}
