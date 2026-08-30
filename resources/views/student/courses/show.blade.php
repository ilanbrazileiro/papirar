@extends('layouts.student')

@section('title', $course->title)

@push('styles')
<style>
    .course-content-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .course-content-summary .summary-item {
        flex: 1 1 150px;
        border: 1px solid rgba(15, 35, 68, .08);
        background: #f7f9fc;
        border-radius: 14px;
        padding: .85rem 1rem;
    }

    .course-content-summary .summary-item span {
        display: block;
        color: #6b7788;
        font-size: .78rem;
    }

    .course-content-summary .summary-item strong {
        display: block;
        color: #0f2344;
        font-size: 1.05rem;
        margin-top: .15rem;
    }

    .course-mobile-action {
        display: none;
    }

    @media (max-width: 767.98px) {
        .course-mobile-action {
            display: block;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1055;
            padding: .7rem .85rem calc(.7rem + env(safe-area-inset-bottom));
            background: rgba(255, 255, 255, .98);
            border-top: 1px solid rgba(15, 35, 68, .12);
            box-shadow: 0 -10px 30px rgba(15, 35, 68, .16);
        }

        .course-mobile-action-inner {
            display: grid;
            grid-template-columns: 1fr 1.35fr;
            gap: .65rem;
        }

        .course-mobile-spacer {
            height: 84px;
        }

        .course-header-actions {
            display: none !important;
        }
    }
</style>
@endpush

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

        <div class="d-flex flex-wrap gap-2 course-header-actions">
            <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary">Meus cursos</a>
            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar</a>
            <a href="{{ route('student.courses.simulated.index', $course) }}" class="btn btn-outline-primary">Simulados</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Questões disponíveis</div>
                <div class="value">{{ $totalQuestions }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Disciplinas</div>
                <div class="value">{{ $subjects->count() }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Tópicos</div>
                <div class="value">{{ $topics->count() }}</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Fontes</div>
                <div class="value">{{ $sourceMaterials->count() }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Conteúdo acadêmico recolhido por padrão no desktop e no mobile --}}
            <div class="card-soft p-4 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="section-title mb-1">Conteúdo do curso</div>
                        <p class="small-muted mb-0">
                            {{ $subjects->count() }} disciplina(s) e {{ $topics->count() }} tópico(s) vinculados.
                        </p>
                    </div>

                    <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#course-content-details"
                        aria-expanded="false"
                        aria-controls="course-content-details"
                    >
                        Ver disciplinas e tópicos
                    </button>
                </div>

                <div class="collapse mt-4" id="course-content-details">
                    <div class="mb-4">
                        <div class="fw-semibold mb-2">Disciplinas</div>

                        @if($subjects->isEmpty())
                            <div class="small-muted">Nenhuma disciplina vinculada ao curso.</div>
                        @else
                            <div class="row g-2">
                                @foreach($subjects as $subject)
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 bg-white">
                                            {{ $subject->name }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="fw-semibold mb-2">Tópicos</div>

                        @if($topics->isEmpty())
                            <div class="small-muted">Nenhum tópico vinculado ao curso.</div>
                        @else
                            <div class="row g-2">
                                @foreach($topics as $topic)
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 bg-white">
                                            {{ $topic->name }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-soft p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Compras deste curso</div>
                    <a href="{{ route('student.purchases.index', ['course_id' => $course->id]) }}" class="btn btn-sm btn-outline-primary">
                        Ver histórico
                    </a>
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

    <div class="course-mobile-spacer d-md-none" aria-hidden="true"></div>

    <div class="course-mobile-action d-md-none">
        <div class="course-mobile-action-inner">
            <a href="{{ route('student.courses.simulated.index', $course) }}" class="btn btn-outline-primary">
                Simulado
            </a>

            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">
                Estudar agora
            </a>
        </div>
    </div>
@endsection
