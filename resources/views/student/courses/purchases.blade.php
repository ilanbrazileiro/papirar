@extends('layouts.student')

@section('title', 'Histórico de compras')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Histórico de compras</h1>
            <p class="page-subtitle">Acompanhe pagamentos, períodos comprados e status de liberação dos cursos.</p>
        </div>
        <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary">Meus cursos</a>
    </div>

    <div class="card-soft p-4 mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label small-muted">Curso</label>
                <select name="course_id" class="form-control">
                    <option value="">Todos os cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) request('course_id') === (int) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small-muted">Status</label>
                <select name="status" class="form-control">
                    <option value="">Todos os status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="card-soft p-4">
        @if($transactions->isEmpty())
            <p class="small-muted mb-0">Nenhuma compra encontrada.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Status</th>
                            <th>Período</th>
                            <th>Valor</th>
                            <th>Pagamento</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            @php
                                $statusLabel = $statuses[$transaction->status] ?? $transaction->status;
                                $badgeClass = match ($transaction->status) {
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    'failed', 'canceled' => 'danger',
                                    'refunded' => 'secondary',
                                    default => 'secondary',
                                };
                                $course = $transaction->course;
                                $cycle = $transaction->subscription?->billing_cycle;
                                $cycleLabel = $course && $cycle ? $course->billingCycleLabel($cycle) : '-';
                                $expiresAt = $transaction->subscription?->expires_at;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $course->title ?? 'Curso removido' }}</strong><br>
                                    <span class="small-muted">Pedido #{{ $transaction->id }}</span>
                                </td>
                                <td><span class="badge text-bg-{{ $badgeClass }}">{{ $statusLabel }}</span></td>
                                <td>
                                    {{ $cycleLabel }}<br>
                                    @if($expiresAt)
                                        <span class="small-muted">Acesso até {{ $expiresAt->format('d/m/Y') }}</span>
                                    @else
                                        <span class="small-muted">Aguardando confirmação</span>
                                    @endif
                                </td>
                                <td>R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                <td>
                                    {{ optional($transaction->paid_at ?: $transaction->created_at)->format('d/m/Y H:i') }}<br>
                                    <span class="small-muted">{{ $transaction->gateway }}</span>
                                </td>
                                <td class="text-end">
                                    @if($transaction->checkoutUrl())
                                        <a href="{{ $transaction->checkoutUrl() }}" class="btn btn-sm btn-primary">Concluir pagamento</a>
                                    @elseif($course)
                                        <a href="{{ route('student.courses.show', $course) }}" class="btn btn-sm btn-outline-primary">Ver curso</a>
                                    @else
                                        <span class="small-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
