@extends('layouts.student')

@section('title', 'Assinatura de cursos')

@push('styles')
<style>
    .courses-hero {
        background: linear-gradient(135deg, #0f2344 0%, #173b72 55%, #f4c542 170%);
        color: #fff;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 18px 45px rgba(15, 35, 68, .16);
    }
    .courses-hero .hero-eyebrow { text-transform: uppercase; letter-spacing: .08em; font-size: .78rem; opacity: .82; font-weight: 700; }
    .courses-hero h1 { font-weight: 800; margin: 8px 0 10px; }
    .courses-hero p { color: rgba(255,255,255,.82); max-width: 760px; }
    .hero-proof { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 18px; }
    .hero-proof span { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18); border-radius: 999px; padding: 8px 12px; font-size: .92rem; }
    .course-commercial-card { background: #fff; border: 1px solid rgba(15,35,68,.08); border-radius: 20px; box-shadow: 0 14px 35px rgba(15,35,68,.08); transition: transform .18s ease, box-shadow .18s ease; }
    .course-commercial-card:hover { transform: translateY(-3px); box-shadow: 0 22px 50px rgba(15,35,68,.12); }
    .course-cover-wrap { position: relative; height: 168px; background: #0f2344; }
    .course-cover-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .course-cover-placeholder { height: 100%; display: flex; align-items: center; gap: 16px; padding: 24px; color: #fff; background: radial-gradient(circle at top right, rgba(244,197,66,.38), transparent 42%), linear-gradient(135deg, #0f2344, #173b72); }
    .course-cover-logo { width: 54px; height: 54px; border-radius: 16px; display: grid; place-items: center; background: #f4c542; color: #0f2344; font-weight: 900; font-size: 1.6rem; }
    .course-badge { position: absolute; top: 14px; left: 14px; background: #f4c542; color: #0f2344; border-radius: 999px; padding: 6px 10px; font-size: .78rem; font-weight: 800; box-shadow: 0 8px 18px rgba(0,0,0,.16); }
    .course-card-title { font-size: 1.16rem; font-weight: 800; color: #0f2344; line-height: 1.25; }
    .course-card-headline { color: #526174; font-size: .94rem; min-height: 44px; }
    .course-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .course-meta-grid > div, .course-price-box { background: #f7f9fc; border: 1px solid #edf1f7; border-radius: 14px; padding: 12px; }
    .course-meta-grid .label { display: block; color: #6b7788; font-size: .76rem; margin-bottom: 4px; }
    .course-meta-grid strong, .course-price-box strong { color: #0f2344; }
    .course-bullets { list-style: none; padding: 0; color: #3c4858; font-size: .92rem; }
    .course-bullets li { margin-bottom: 7px; padding-left: 22px; position: relative; }
    .course-bullets li::before { content: '✓'; position: absolute; left: 0; color: #0f8a4b; font-weight: 800; }
    .trust-strip { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    .trust-strip .item { background: #fff; border: 1px solid rgba(15,35,68,.08); border-radius: 16px; padding: 16px; }
    .trust-strip strong { color: #0f2344; display: block; }
    @media (max-width: 768px) { .courses-hero { padding: 24px; } .trust-strip { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    @php
        $paymentStatus = $paymentStatus ?? request('payment') ?? request('status');
        $pendingTransactions = $pendingTransactions ?? collect();
        $activeCourseAccesses = $activeCourseAccesses ?? collect();
        $availableCourses = $availableCourses ?? collect();
        $recentTransactions = $recentTransactions ?? collect();
        $courseQuestionCounts = $courseQuestionCounts ?? [];
    @endphp

    <div class="courses-hero mb-4">
        <div class="hero-eyebrow">Assinatura por curso</div>
        <h1>Escolha, renove ou amplie seus cursos.</h1>
        <p class="mb-0">
            No Papirar, cada assinatura libera acesso a um curso específico, com questões direcionadas,
            comentários, simulados, favoritas e acompanhamento de desempenho.
        </p>
        <div class="hero-proof">
            <span>Compra por curso</span>
            <span>Renovação do acesso</span>
            <span>Ampliação de período</span>
            <span>Novos cursos disponíveis</span>
        </div>
    </div>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Assinatura de cursos</h1>
            <p class="page-subtitle mb-0">
                Use esta tela para assinar um novo curso, renovar um curso ativo ou ampliar seu tempo de acesso.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary">Meus cursos</a>
            <a href="{{ route('student.purchases.index') }}" class="btn btn-outline-primary">Histórico de compras</a>
        </div>
    </div>

    @if($needsEmailVerification ?? false)
        <div class="alert alert-warning rounded-4 mb-4">
            <strong>Confirme seu e-mail.</strong><br>
            Você já pode conhecer os cursos disponíveis, mas recomendamos confirmar seu e-mail antes de iniciar uma compra.
            <form method="POST" action="{{ route('auth.verification.resend') }}" class="mt-3">
                @csrf
                <button class="btn btn-sm btn-warning">Reenviar confirmação</button>
            </form>
        </div>
    @endif

    @if($paymentStatus === 'success')
        <div class="alert alert-success">Pagamento aprovado ou em processamento. O acesso será liberado automaticamente após confirmação do Mercado Pago.</div>
    @elseif($paymentStatus === 'pending')
        <div class="alert alert-warning">Pagamento pendente. Assim que for confirmado, o acesso será atualizado.</div>
    @elseif($paymentStatus === 'failure')
        <div class="alert alert-danger">Pagamento não concluído. Tente novamente ou escolha outro período.</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if($pendingTransactions->isNotEmpty())
        <div class="card-soft p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title mb-0">Pagamentos pendentes</div>
                <a href="{{ route('student.purchases.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
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
                                        <a href="{{ $transaction->checkoutUrl() }}" class="btn btn-sm btn-primary">Concluir pagamento</a>
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

    <div class="trust-strip mb-4">
        <div class="item">
            <strong>Assinatura por curso</strong>
            <span class="small-muted">Você escolhe exatamente o curso que precisa estudar.</span>
        </div>
        <div class="item">
            <strong>Renovação simples</strong>
            <span class="small-muted">Alunos com acesso ativo podem renovar ou ampliar o período.</span>
        </div>
        <div class="item">
            <strong>Venda cruzada</strong>
            <span class="small-muted">Outros cursos disponíveis aparecem para ampliar a preparação.</span>
        </div>
    </div>

    <div class="section-title mb-3">Meus cursos ativos</div>

    @if($activeCourseAccesses->isEmpty())
        <div class="card-soft p-4 mb-4">
            <div class="section-title mb-2">Você ainda não possui curso ativo</div>
            <p class="small-muted mb-0">
                Escolha um dos cursos disponíveis abaixo para iniciar sua preparação no Papirar.
            </p>
        </div>
    @else
        <div class="row g-4 mb-5">
            @foreach($activeCourseAccesses as $access)
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

    <div class="section-title mb-3">
        {{ $activeCourseAccesses->isEmpty() ? 'Cursos disponíveis para assinatura' : 'Outros cursos disponíveis para assinatura' }}
    </div>

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
            <a href="{{ route('student.purchases.index') }}" class="btn btn-sm btn-outline-primary">Histórico completo</a>
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

        @if($recentTransactions->isEmpty())
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
