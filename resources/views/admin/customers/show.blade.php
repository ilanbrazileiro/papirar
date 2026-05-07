@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">{{ $customer->name }}</h1>
        <div class="small-muted">{{ $customer->email }}</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('admin.customers.index') }}">Voltar</a>
        <a class="btn btn-primary" href="{{ route('admin.customers.edit', $customer) }}">Editar cliente</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-soft p-4 h-100">
            <h5 class="fw-bold mb-3">Dados do cliente</h5>
            <div class="mb-2"><strong>Nome:</strong> {{ $customer->name }}</div>
            <div class="mb-2"><strong>E-mail:</strong> {{ $customer->email }}</div>
            <div class="mb-2"><strong>CPF:</strong> {{ $customer->cpf ?: 'Não informado' }}</div>
            <div class="mb-2"><strong>Telefone:</strong> {{ $customer->phone ?: 'Não informado' }}</div>
            <div class="mb-2"><strong>Conta:</strong>
                @if($customer->is_active)
                    <span class="badge text-bg-success">Ativa</span>
                @else
                    <span class="badge text-bg-secondary">Inativa</span>
                @endif
            </div>
            <div class="mb-2"><strong>Cadastro:</strong> {{ $customer->created_at?->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-soft p-4 h-100">
            <h5 class="fw-bold mb-3">Liberar acesso manual</h5>
            <form method="POST" action="{{ route('admin.customers.grant-access', $customer) }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Dias de acesso</label>
                    <input type="number" name="days" min="1" max="365" value="7" class="form-control" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Plano vinculado</label>
                    <select name="plan_id" class="form-select">
                        <option value="">Liberação manual</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} - R$ {{ number_format((float) $plan->price, 2, ',', '.') }} / {{ $plan->duration_days }} dias</option>
                        @endforeach
                    </select>
                    <div class="form-text">Se não escolher um plano, o sistema cria/usa o plano interno “Liberação manual”.</div>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cancel_current" value="1" id="cancel_current">
                        <label class="form-check-label" for="cancel_current">
                            Cancelar assinaturas ativas atuais antes de liberar este acesso
                        </label>
                    </div>
                    <div class="form-text">Deixe desmarcado para somar os dias ao vencimento atual do cliente.</div>
                </div>

                <div class="col-12">
                    <button class="btn btn-success">Liberar acesso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card-soft p-4 mt-4">
    <h5 class="fw-bold mb-3">Histórico de assinaturas/acessos</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plano</th>
                    <th>Status</th>
                    <th>Início</th>
                    <th>Vencimento</th>
                    <th>Cancelado em</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->subscriptions as $subscription)
                    <tr>
                        <td>#{{ $subscription->id }}</td>
                        <td>{{ $subscription->plan?->name ?: '-' }}</td>
                        <td>
                            @if($subscription->isActive())
                                <span class="badge text-bg-success">Ativa</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $subscription->status }}</span>
                            @endif
                        </td>
                        <td>{{ $subscription->starts_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td>{{ $subscription->expires_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td>{{ $subscription->canceled_at?->format('d/m/Y H:i') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Nenhuma assinatura encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
