@extends('layouts.student')

@section('title', 'Assinatura de cursos')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Assinatura de cursos</h1>
            <p class="page-subtitle">
                Escolha um curso para começar, renovar seu acesso ou ampliar sua preparação para outros concursos.
            </p>
        </div>
        <a href="{{ route('student.purchases.index') }}" class="btn btn-outline-primary">Ver compras</a>
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

    @if ($paymentStatus === 'success')
        <div class="alert alert-success">Pagamento enviado ao Mercado Pago. Assim que houver confirmação, o acesso ao curso será liberado automaticamente.</div>
    @elseif ($paymentStatus === 'pending')
        <div class="alert alert-warning">Seu pagamento está pendente. Assim que houver confirmação, o acesso ao curso será ativado.</div>
    @elseif ($paymentStatus === 'failure')
        <div class="alert alert-danger">O pagamento não foi concluído. Você pode tentar novamente.</div>
    @endif

    @if(($pendingTransactions ?? collect())->count())
        <div class="card-soft p-4 mb-4 border border-warning-subtle bg-warning-subtle bg-opacity-25">
            <div class="section-title mb-2">Compras pendentes</div>
            <div class="row g-3">
                @foreach($pendingTransactions as $transaction)
                    <div class="col-md-6 col-xl-4">
                        <div class="border rounded-4 p-3 bg-white h-100">
                            <div class="fw-semibold">{{ $transaction->course->title ?? 'Curso' }}</div>
                            <div class="small-muted mb-2">Status: {{ strtoupper($transaction->status) }}</div>
                            <a href="{{ route('student.purchases.index') }}" class="btn btn-sm btn-outline-warning w-100">Ver compra</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(($activeCourseAccesses ?? collect())->count())
        <div class="card-soft p-4 mb-4">
            <div class="section-title mb-1">Meus cursos ativos</div>
            <p class="small-muted mb-4">
                Renove seu acesso, amplie o período de estudo ou continue normalmente pelo curso.
            </p>

            <div class="row g-4">
                @foreach($activeCourseAccesses as $access)
                    @php
                        $course = $access->course;
                        $cycles = $course?->availableBillingCycles() ?? [];
                    @endphp

                    @if($course)
                        <div class="col-lg-6">
                            <div class="border rounded-4 p-4 bg-white h-100">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <div>
                                        <div class="fw-bold fs-5">{{ $course->title }}</div>
                                        <div class="small-muted">
                                            {{ $access->accessTypeLabel() }} · acesso até:
                                            <strong>{{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</strong>
                                        </div>
                                    </div>
                                    <span class="badge bg-success align-self-start">Ativo</span>
                                </div>

                                <div class="d-flex flex-wrap gap-2 my-3">
                                    <a href="{{ route('student.courses.show', $course) }}" class="btn btn-sm btn-primary">Entrar no curso</a>
                                    <a href="{{ route('student.courses.study', $course) }}" class="btn btn-sm btn-outline-primary">Estudar</a>
                                </div>

                                <hr>

                                <div class="fw-semibold mb-2">Renovar ou ampliar período</div>

                                @if(empty($cycles))
                                    <div class="small-muted">Este curso ainda não possui preço disponível.</div>
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
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <div class="card-soft p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
            <div>
                <div class="section-title mb-1">
                    {{ ($activeCourseAccesses ?? collect())->count() ? 'Outros cursos disponíveis' : 'Cursos disponíveis para assinatura' }}
                </div>
                <div class="small-muted">
                    Escolha o curso que melhor combina com seu objetivo e comece a treinar com questões direcionadas.
                </div>
            </div>
        </div>

        <div class="row g-4">
            @forelse($availableCourses as $course)
                <div class="col-md-6 col-xl-4">
                    @include('student.courses.partials.commercial-card', [
                        'course' => $course,
                        'access' => null,
                        'questionCount' => 0,
                        'mode' => 'available',
                    ])
                </div>
            @empty
                <div class="col-12">
                    <div class="border rounded-4 p-4 bg-white">
                        Nenhum curso disponível para assinatura no momento.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
