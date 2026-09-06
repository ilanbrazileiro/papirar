@extends('layouts.student')

@section('title', 'Meus cursos')

@push('styles')
<style>
    .course-commercial-card {
        background: #fff;
        border: 1px solid rgba(15,35,68,.08);
        border-radius: 20px;
        box-shadow: 0 14px 35px rgba(15,35,68,.08);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .course-commercial-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 50px rgba(15,35,68,.12);
    }

    .course-cover-wrap {
        position: relative;
        height: 168px;
        background: #0f2344;
    }

    .course-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .course-cover-placeholder {
        height: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(244,197,66,.38), transparent 42%),
            linear-gradient(135deg, #0f2344, #173b72);
    }

    .course-cover-logo {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        background: #f4c542;
        color: #0f2344;
        font-weight: 900;
        font-size: 1.6rem;
    }

    .course-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        background: #f4c542;
        color: #0f2344;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .78rem;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(0,0,0,.16);
    }

    .course-card-title {
        font-size: 1.16rem;
        font-weight: 800;
        color: #0f2344;
        line-height: 1.25;
    }

    .course-card-headline {
        color: #526174;
        font-size: .94rem;
        min-height: 44px;
    }

    .course-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .course-meta-grid > div,
    .course-price-box {
        background: #f7f9fc;
        border: 1px solid #edf1f7;
        border-radius: 14px;
        padding: 12px;
    }

    .course-meta-grid .label {
        display: block;
        color: #6b7788;
        font-size: .76rem;
        margin-bottom: 4px;
    }

    .course-meta-grid strong,
    .course-price-box strong {
        color: #0f2344;
    }

    .course-bullets {
        list-style: none;
        padding: 0;
        color: #3c4858;
        font-size: .92rem;
    }

    .course-bullets li {
        margin-bottom: 7px;
        padding-left: 22px;
        position: relative;
    }

    .course-bullets li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #0f8a4b;
        font-weight: 800;
    }
    .trial-entry-hero { margin-bottom:2rem; padding:28px; border-radius:24px; color:#fff; background:radial-gradient(circle at top right,rgba(244,197,66,.3),transparent 34%),linear-gradient(135deg,#0f2344,#08172d); box-shadow:0 20px 50px rgba(15,35,68,.18); }
    .trial-entry-kicker { display:inline-block; margin-bottom:8px; color:#f4c542; font-size:.75rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .trial-entry-hero h1 { max-width:760px; margin-bottom:8px; font-size:clamp(1.75rem,3.5vw,2.55rem); line-height:1.08; }
    .trial-entry-lead { max-width:760px; margin-bottom:20px; color:#dbe7f7; }
    .trial-direction { display:flex; align-items:center; gap:9px; margin:-5px 0 14px; color:#fff; font-size:.88rem; font-weight:800; }
    .trial-direction-arrow { display:inline-block; color:#f4c542; font-size:1.55rem; line-height:1; }
    .trial-direction-arrow-desktop { animation:trialArrowRight 1.8s ease-in-out infinite; }
    .trial-direction-arrow-mobile { display:none; animation:trialArrowDown 1.6s ease-in-out infinite; }
    .trial-quick-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .trial-quick-card { display:flex; flex-direction:column; min-width:0; padding:16px; border:1px solid rgba(255,255,255,.16); border-radius:17px; background:rgba(255,255,255,.1); backdrop-filter:blur(6px); }
    .trial-quick-card h2 { margin-bottom:5px; color:#fff; font-size:1.05rem; line-height:1.3; }
    .trial-quick-card p { margin-bottom:13px; color:#cbd8ea; font-size:.82rem; }
    .trial-quick-card form { margin-top:auto; }
    .trial-primary-button { padding:13px 16px; border:0; background:#f4c542; color:#0f2344; font-weight:900; box-shadow:0 10px 24px rgba(244,197,66,.24); }
    .trial-button-arrow { display:inline-block; margin-left:7px; transition:transform .18s ease; animation:trialArrowRight 1.8s ease-in-out infinite; }
    .trial-primary-button:hover .trial-button-arrow,
    .btn-warning:hover .trial-button-arrow { animation:none; transform:translateX(4px); }
    .trial-entry-footer { display:flex; justify-content:space-between; gap:14px; align-items:center; margin-top:16px; color:#cbd8ea; font-size:.82rem; }
    .trial-entry-footer a { color:#fff; font-weight:800; text-decoration:underline; }

    @keyframes trialArrowRight {
        0%, 65%, 100% { transform:translateX(0); }
        78% { transform:translateX(5px); }
    }

    @keyframes trialArrowDown {
        0%, 60%, 100% { transform:translateY(0); }
        75% { transform:translateY(5px); }
    }

    @media (prefers-reduced-motion:reduce) {
        .trial-button-arrow,
        .trial-direction-arrow { animation:none; }
    }

    @media (max-width:991.98px) { .trial-quick-grid { grid-template-columns:1fr; } }
    @media (max-width:575.98px) {
        .trial-entry-hero { margin:-8px -4px 1.5rem; padding:21px 16px; border-radius:19px; }
        .trial-entry-lead { margin-bottom:14px; font-size:.9rem; }
        .trial-quick-card { padding:14px; }
        .trial-quick-card:not(:first-child) { display:none; }
        .trial-entry-footer { align-items:flex-start; flex-direction:column; }
        .trial-direction-arrow-desktop { display:none; }
        .trial-direction-arrow-mobile { display:inline-block; }
    }
</style>
@endpush

@section('content')
    @php
        $paymentStatus = $paymentStatus ?? request('status');
        $pendingTransactions = $pendingTransactions ?? collect();
        $courseAccesses = $courseAccesses ?? collect();
        $availableCourses = $availableCourses ?? collect();
        $recentTransactions = $recentTransactions ?? collect();
        $courseQuestionCounts = $courseQuestionCounts ?? [];
        $trialUsedCourseIds = collect($trialUsedCourseIds ?? []);
        $trialOfferCourses = $availableCourses
            ->filter(fn ($course) => (bool) $course->is_trial_available
                && (int) $course->trial_days > 0
                && !$trialUsedCourseIds->contains((int) $course->id))
            ->take(3);
    @endphp

    @if($courseAccesses->isNotEmpty())
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Meus cursos</h1>
                <p class="page-subtitle mb-0">Continue estudando nos cursos em que você possui acesso.</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('student.purchases.index') }}" class="btn btn-outline-primary">Histórico de compras</a>
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">Dashboard</a>
            </div>
        </div>
    @endif

    @if($paymentStatus === 'success')
        <div class="alert alert-success">
            Pagamento aprovado ou em processamento. O acesso será liberado automaticamente após confirmação do Mercado Pago.
        </div>
    @elseif($paymentStatus === 'pending')
        <div class="alert alert-warning">
            Pagamento pendente. Assim que for confirmado, o acesso será atualizado.
        </div>
    @elseif($paymentStatus === 'failure')
        <div class="alert alert-danger">
            Pagamento não concluído. Tente novamente ou escolha outro período.
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($courseAccesses->isEmpty() && $trialOfferCourses->isNotEmpty())
        <section class="trial-entry-hero" aria-labelledby="trial-entry-title">
            <span class="trial-entry-kicker">Seu próximo passo</span>
            <h1 id="trial-entry-title">Escolha seu curso e estude grátis por 7 dias</h1>
            <p class="trial-entry-lead">
                Suas respostas foram salvas. Experimente a plataforma completa, com comentários, simulados e recursos de acompanhamento.
            </p>

            <div class="trial-direction">
                <span>Escolha um curso abaixo e comece agora</span>
                <span class="trial-direction-arrow trial-direction-arrow-desktop" aria-hidden="true">→</span>
                <span class="trial-direction-arrow trial-direction-arrow-mobile" aria-hidden="true">↓</span>
            </div>

            <div class="trial-quick-grid">
                @foreach($trialOfferCourses as $course)
                    <article class="trial-quick-card">
                        <h2>{{ $course->title }}</h2>
                        <p>
                            {{ $course->trialDaysForAccess() }} dias de acesso completo · sem cartão
                            @if($course->bestCommercialPriceLabel())
                                <br>Depois, {{ $course->bestCommercialPriceLabel() }}
                            @endif
                        </p>
                        <form method="POST" action="{{ route('student.courses.trial.start', $course) }}">
                            @csrf
                            <button class="btn trial-primary-button w-100" onclick="return confirm('Deseja iniciar o teste gratuito deste curso por {{ $course->trialDaysForAccess() }} dias?');">
                                Começar teste grátis de {{ $course->trialDaysForAccess() }} dias
                                <span class="trial-button-arrow" aria-hidden="true">→</span>
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>

            <div class="trial-entry-footer">
                <span>A ativação é imediata e não inicia cobrança automática.</span>
                <a href="#available-courses">Ver detalhes e todos os cursos</a>
            </div>
        </section>
    @elseif($courseAccesses->isEmpty())
        <div class="mb-4">
            <h1 class="page-title mb-1">Escolha seu curso</h1>
            <p class="page-subtitle mb-0">Veja as opções disponíveis para continuar estudando.</p>
        </div>
    @endif

    {{-- Quando há acesso, os cursos ativos continuam sendo a prioridade. --}}
    @if($courseAccesses->isNotEmpty())
        <div class="section-title mb-3">Cursos ativos</div>
        <div class="row g-4 mb-5">
            @foreach($courseAccesses as $access)
                @php
                    $course = $access->course;
                    $questionCount = $course ? ($courseQuestionCounts[$course->id] ?? 0) : 0;
                @endphp

                @if($course)
                    <div class="col-md-6 col-xl-4">
                        @include('student.courses.partials.commercial-card', [
                            'course' => $course,
                            'access' => $access,
                            'questionCount' => $questionCount,
                            'mode' => 'active',
                        ])
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if($pendingTransactions->isNotEmpty())
        <div class="card-soft p-4 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title mb-0">Pagamentos pendentes</div>
                <a href="{{ route('student.purchases.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Valor</th>
                            <th>Período</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingTransactions as $transaction)
                            @php
                                $cycle = $transaction->subscription?->billing_cycle;
                                $cycleLabel = $transaction->course?->billingCycleLabel($cycle ?: 'monthly') ?? $cycle;
                            @endphp

                            <tr>
                                <td>{{ $transaction->course->title ?? 'Curso removido' }}</td>
                                <td>R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                <td>{{ $cycleLabel ?: '-' }}</td>
                                <td class="text-end">
                                    @if($transaction->checkoutUrl())
                                        <a href="{{ $transaction->checkoutUrl() }}" class="btn btn-sm btn-primary">
                                            Concluir pagamento
                                        </a>
                                    @else
                                        <span class="small-muted">Checkout indisponível</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="section-title mb-3" id="available-courses">Cursos disponíveis para assinatura</div>

    @if($availableCourses->isEmpty())
        <div class="card-soft p-4 mb-5">
            <p class="small-muted mb-0">Nenhum curso público disponível para assinatura no momento.</p>
        </div>
    @else
        <div class="row g-4 mb-5">
            @foreach($availableCourses as $course)
                @php
                    $questionCount = $courseQuestionCounts[$course->id] ?? 0;
                @endphp

                <div class="col-md-6 col-xl-4">
                    @include('student.courses.partials.commercial-card', [
                        'course' => $course,
                        'access' => null,
                        'questionCount' => $questionCount,
                        'mode' => 'available',
                    ])
                </div>
            @endforeach
        </div>
    @endif

    <div class="card-soft p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Últimas compras</div>
            <a href="{{ route('student.purchases.index') }}" class="btn btn-sm btn-outline-primary">
                Histórico completo
            </a>
        </div>

        @php
            $transactionStatusLabels = [
                'pending' => 'Pendente',
                'paid' => 'Pago',
                'approved' => 'Pago',
                'failed' => 'Falhou',
                'rejected' => 'Rejeitado',
                'refunded' => 'Reembolsado',
                'canceled' => 'Cancelado',
                'cancelled' => 'Cancelado',
            ];
        @endphp

        @if(($recentTransactions ?? collect())->isEmpty())
            <p class="small-muted mb-0">Nenhuma compra registrada ainda.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->course->title ?? 'Curso removido' }}</td>
                                <td>{{ $transactionStatusLabels[$transaction->status] ?? ucfirst((string) $transaction->status) }}</td>
                                <td>R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                <td>{{ optional($transaction->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
