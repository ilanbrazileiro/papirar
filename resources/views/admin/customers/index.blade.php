@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Clientes</h1>
        <div class="small-muted">Consulte clientes ativos, inativos e libere acesso manual quando necessário.</div>
    </div>
</div>

<div class="card-soft p-4 mb-4">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nome, e-mail ou CPF">
        </div>

        <div class="col-md-3">
            <label class="form-label">Acesso</label>
            <select name="access_status" class="form-select">
                <option value="">Todos</option>
                <option value="active" @selected(request('access_status') === 'active')>Com acesso ativo</option>
                <option value="inactive" @selected(request('access_status') === 'inactive')>Sem acesso ativo</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Conta</label>
            <select name="account_status" class="form-select">
                <option value="">Todas</option>
                <option value="enabled" @selected(request('account_status') === 'enabled')>Conta ativa</option>
                <option value="disabled" @selected(request('account_status') === 'disabled')>Conta inativa</option>
            </select>
        </div>

        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </form>
</div>

<div class="card-soft p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>E-mail</th>
                    <th>Conta</th>
                    <th>Acesso</th>
                    <th>Vencimento</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    @php
                        $activeSubscription = $customer->subscriptions
                            ->first(fn ($subscription) => $subscription->isActive());
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                            <div class="small-muted">CPF: {{ $customer->cpf ?: 'Não informado' }}</div>
                        </td>
                        <td>{{ $customer->email }}</td>
                        <td>
                            @if($customer->is_active)
                                <span class="badge text-bg-success">Ativa</span>
                            @else
                                <span class="badge text-bg-secondary">Inativa</span>
                            @endif
                        </td>
                        <td>
                            @if($activeSubscription)
                                <span class="badge text-bg-primary">Com acesso</span>
                                <div class="small-muted">{{ $activeSubscription->plan?->name }}</div>
                            @else
                                <span class="badge text-bg-warning">Sem acesso</span>
                            @endif
                        </td>
                        <td>
                            @if($activeSubscription?->expires_at)
                                {{ $activeSubscription->expires_at->format('d/m/Y H:i') }}
                            @else
                                <span class="small-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.customers.show', $customer) }}">Ver</a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.customers.edit', $customer) }}">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Nenhum cliente encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $customers->links() }}
</div>
@endsection
