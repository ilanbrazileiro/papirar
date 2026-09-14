@extends('layouts.admin')

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
            <div class="mb-2"><strong>Último login:</strong> {{ $customer->last_login_at?->format('d/m/Y H:i') ?: 'Sem registro' }}</div>
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
                    <select name="plan_id" class="form-control">
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
                        <label class="form-check-label" for="cancel_current">Cancelar assinaturas ativas atuais antes de liberar este acesso</label>
                    </div>
                    <div class="form-text">Deixe desmarcado para somar os dias ao vencimento atual do cliente.</div>
                </div>

                <div class="col-12"><button class="btn btn-success">Liberar acesso</button></div>
            </form>
        </div>
    </div>
</div>

<div class="card-soft p-4 mt-4" id="uso-papirar">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Uso do Papirar</h5>
            <div class="small-muted">Atividade real de estudo do cliente no período selecionado.</div>
        </div>

        <div class="btn-group flex-wrap" role="group" aria-label="Período do relatório">
            <a href="{{ route('admin.customers.show', ['customer' => $customer, 'period' => 7]) }}#uso-papirar" class="btn btn-sm {{ $usage->period === '7' ? 'btn-primary' : 'btn-outline-primary' }}">7 dias</a>
            <a href="{{ route('admin.customers.show', ['customer' => $customer, 'period' => 30]) }}#uso-papirar" class="btn btn-sm {{ $usage->period === '30' ? 'btn-primary' : 'btn-outline-primary' }}">30 dias</a>
            <a href="{{ route('admin.customers.show', ['customer' => $customer, 'period' => 90]) }}#uso-papirar" class="btn btn-sm {{ $usage->period === '90' ? 'btn-primary' : 'btn-outline-primary' }}">90 dias</a>
            <a href="{{ route('admin.customers.show', ['customer' => $customer, 'period' => 'all']) }}#uso-papirar" class="btn btn-sm {{ $usage->period === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">Todo período</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="border rounded-4 p-3 h-100 bg-light"><div class="small-muted">Dias ativos</div><div class="fs-3 fw-bold">{{ $usage->active_days }}</div></div></div>
        <div class="col-6 col-xl-3"><div class="border rounded-4 p-3 h-100 bg-light"><div class="small-muted">Respostas</div><div class="fs-3 fw-bold">{{ $usage->answered }}</div><div class="small-muted">{{ $usage->distinct_questions }} questões distintas</div></div></div>
        <div class="col-6 col-xl-3"><div class="border rounded-4 p-3 h-100 bg-light"><div class="small-muted">Aproveitamento</div><div class="fs-3 fw-bold">{{ number_format($usage->accuracy, 1, ',', '.') }}%</div><div class="small-muted">{{ $usage->correct }} certas · {{ $usage->wrong }} erradas</div></div></div>
        <div class="col-6 col-xl-3"><div class="border rounded-4 p-3 h-100 bg-light"><div class="small-muted">Simulados finalizados</div><div class="fs-3 fw-bold">{{ $usage->simulations_finished }}</div><div class="small-muted">{{ $usage->simulations }} criados</div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><div class="small-muted">Sessões de estudo</div><div class="fw-bold fs-5">{{ $usage->sessions }}</div><div class="small-muted">{{ $usage->finished_sessions }} concluídas</div></div></div>
        <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><div class="small-muted">Questões por dia ativo</div><div class="fw-bold fs-5">{{ number_format($usage->questions_per_active_day, 1, ',', '.') }}</div><div class="small-muted">média no período</div></div></div>
        <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><div class="small-muted">Última atividade de estudo</div><div class="fw-bold fs-6">{{ $usage->last_study_activity_at ? \Carbon\Carbon::parse($usage->last_study_activity_at)->format('d/m/Y H:i') : 'Sem atividade' }}</div><div class="small-muted">não confundir com simples login</div></div></div>
    </div>

    <h6 class="fw-bold mb-3">Uso por curso</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Curso</th><th class="text-center">Sessões</th><th class="text-center">Respostas</th><th class="text-center">Questões distintas</th><th class="text-center">Acerto</th><th class="text-center">Simulados</th><th class="text-end">Última atividade</th></tr></thead>
            <tbody>
                @forelse($courseUsage as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row->course_title }}</td>
                        <td class="text-center">{{ $row->sessions }}</td>
                        <td class="text-center">{{ $row->answered }}</td>
                        <td class="text-center">{{ $row->distinct_questions }}</td>
                        <td class="text-center">{{ number_format($row->accuracy, 1, ',', '.') }}%</td>
                        <td class="text-center">{{ $row->simulations_finished }}/{{ $row->simulations }}</td>
                        <td class="text-end">{{ $row->last_activity_at ? \Carbon\Carbon::parse($row->last_activity_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma atividade de estudo encontrada no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-soft p-4 mt-4">
    <h5 class="fw-bold mb-3">Acessos aos cursos</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Curso</th><th>Tipo</th><th>Status</th><th>Início</th><th>Fim</th></tr></thead>
            <tbody>
                @forelse($courseAccesses as $access)
                    @php
                        $accessType = ['manual' => 'Manual', 'trial' => 'Teste grátis', 'paid' => 'Pago', 'bonus' => 'Bônus'][$access->access_type] ?? ($access->access_type ?: '-');
                        $statusLabel = ['pending' => 'Pendente', 'active' => 'Ativo', 'expired' => 'Expirado', 'canceled' => 'Cancelado'][$access->status] ?? $access->status;
                    @endphp
                    <tr>
                        <td>{{ $access->course_title ?: ('Curso #' . $access->course_id) }}</td>
                        <td>{{ $accessType }}</td>
                        <td><span class="badge {{ $access->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $statusLabel }}</span></td>
                        <td>{{ $access->starts_at ? \Carbon\Carbon::parse($access->starts_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $access->ends_at ? \Carbon\Carbon::parse($access->ends_at)->format('d/m/Y H:i') : 'Sem limite' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum acesso por curso encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-soft p-4 mt-4">
    <h5 class="fw-bold mb-3">Histórico de assinaturas/acessos</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Plano</th><th>Status</th><th>Início</th><th>Vencimento</th><th>Cancelado em</th></tr></thead>
            <tbody>
                @forelse($customer->subscriptions as $subscription)
                    <tr>
                        <td>#{{ $subscription->id }}</td>
                        <td>{{ $subscription->plan?->name ?: '-' }}</td>
                        <td>@if($subscription->isActive())<span class="badge text-bg-success">Ativa</span>@else<span class="badge text-bg-secondary">{{ $subscription->status }}</span>@endif</td>
                        <td>{{ $subscription->starts_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td>{{ $subscription->expires_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td>{{ $subscription->canceled_at?->format('d/m/Y H:i') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma assinatura encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
