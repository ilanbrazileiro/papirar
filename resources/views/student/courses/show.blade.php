@extends('layouts.student')

@section('title', $course->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $course->title }}</h1>
            <p class="page-subtitle">{{ $course->short_description ?: 'Curso liberado para estudo por questões.' }}</p>
            <div class="small-muted mt-1">
                Acesso: <strong>{{ $access->accessTypeLabel() }}</strong> ·
                Status: <strong>{{ $access->statusLabel() }}</strong> ·
                Até: <strong>{{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</strong>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary">Meus cursos</a>
            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar</a>
            <a href="{{ route('student.courses.simulated.index', $course) }}" class="btn btn-outline-primary">Simulados</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Questões disponíveis</div><div class="value">{{ $totalQuestions }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Disciplinas</div><div class="value">{{ $subjects->count() }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Tópicos</div><div class="value">{{ $topics->count() }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Fontes</div><div class="value">{{ $sourceMaterials->count() }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Disciplinas do curso</div>
                @if($subjects->isEmpty())
                    <div class="small-muted">Nenhuma disciplina vinculada ao curso.</div>
                @else
                    <div class="row g-2">
                        @foreach($subjects as $subject)
                            <div class="col-md-6"><div class="border rounded-4 p-3 bg-white">{{ $subject->name }}</div></div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card-soft p-4 mb-4">
                <div class="section-title">Tópicos do curso</div>
                @if($topics->isEmpty())
                    <div class="small-muted">Nenhum tópico vinculado ao curso.</div>
                @else
                    <div class="row g-2">
                        @foreach($topics as $topic)
                            <div class="col-md-6"><div class="border rounded-4 p-3 bg-white">{{ $topic->name }}</div></div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card-soft p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Compras deste curso</div>
                    <a href="{{ route('student.purchases.index', ['course_id' => $course->id]) }}" class="btn btn-sm btn-outline-primary">Ver histórico</a>
                </div>

                @if($transactions->isEmpty())
                    <p class="small-muted mb-0">Nenhuma compra registrada para este curso.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Período</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    @php
                                        $statusLabel = [
                                            'pending' => 'Pendente',
                                            'paid' => 'Pago',
                                            'failed' => 'Falhou',
                                            'refunded' => 'Reembolsado',
                                            'canceled' => 'Cancelado',
                                        ][$transaction->status] ?? $transaction->status;
                                        $cycle = $transaction->subscription?->billing_cycle;
                                        $cycleLabel = $cycle ? $course->billingCycleLabel($cycle) : '-';
                                    @endphp
                                    <tr>
                                        <td>{{ $statusLabel }}</td>
                                        <td>{{ $cycleLabel }}</td>
                                        <td>R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                        <td>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            @if($transaction->checkoutUrl())
                                                <a href="{{ $transaction->checkoutUrl() }}" class="btn btn-sm btn-primary">Pagar</a>
                                            @else
                                                <span class="small-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Ações</div>
                <div class="d-grid gap-2">
                    <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Iniciar estudo</a>
                    <a href="{{ route('student.courses.simulated.index', $course) }}" class="btn btn-outline-primary">Criar simulado</a>
                    <a href="{{ route('student.courses.performance', $course) }}" class="btn btn-outline-primary">Ver desempenho</a>
                    <a href="{{ route('student.courses.favorites.index', $course) }}" class="btn btn-outline-warning">Questões favoritas</a>
                </div>
            </div>

            <div class="card-soft p-4 mb-4">
                <div class="section-title">Renovar ou ampliar acesso</div>
                <p class="small-muted">Comprar um novo período soma dias ao acesso atual quando ele ainda está ativo.</p>
                @php($cycles = $course->availableBillingCycles())
                @if(empty($cycles))
                    <div class="small-muted">Nenhum preço disponível para este curso.</div>
                @else
                    <div class="d-grid gap-2">
                        @foreach($cycles as $cycle => $label)
                            <form method="POST" action="{{ route('student.courses.checkout', $course) }}">
                                @csrf
                                <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                                <button class="btn btn-outline-primary w-100">
                                    {{ $label }} — R$ {{ number_format($course->priceForBillingCycle($cycle), 2, ',', '.') }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card-soft p-4 mb-4">
                <div class="section-title">Detalhes do acesso</div>
                <ul class="list-clean mb-0">
                    <li class="py-2">Tipo: <strong>{{ $access->accessTypeLabel() }}</strong></li>
                    <li class="py-2">Status: <strong>{{ $access->statusLabel() }}</strong></li>
                    <li class="py-2">Início: <strong>{{ $access->starts_at ? $access->starts_at->format('d/m/Y') : '-' }}</strong></li>
                    <li class="py-2">Fim: <strong>{{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</strong></li>
                    <li class="py-2">Bônus: <strong>{{ $access->bonus_days ?? 0 }} dia(s)</strong></li>
                </ul>
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Fontes/Bibliografias</div>
                @if($sourceMaterials->isEmpty())
                    <div class="small-muted">Este curso ainda não possui filtro por fonte.</div>
                @else
                    <ul class="list-clean">
                        @foreach($sourceMaterials as $sourceMaterial)
                            <li class="py-2">{{ $sourceMaterial->title }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection
