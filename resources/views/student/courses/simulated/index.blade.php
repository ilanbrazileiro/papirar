@extends('layouts.student')

@section('title', 'Meus cursos')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Meus cursos</h1>
            <p class="page-subtitle">Acesse seus cursos ativos ou assine um novo curso.</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">Voltar ao dashboard</a>
    </div>

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

    <div class="section-title mb-3">Cursos ativos</div>

    @if($courseAccesses->isEmpty())
        <div class="card-soft p-4 mb-4">
            <div class="section-title mb-2">Nenhum curso ativo encontrado</div>
            <p class="small-muted mb-0">
                Você ainda não possui acesso ativo a cursos. Escolha um curso disponível abaixo para assinar.
            </p>
        </div>
    @else
        <div class="row g-4 mb-5">
            @foreach($courseAccesses as $access)
                @php
                    $course = $access->course;
                    $questionCount = $course ? ($courseQuestionCounts[$course->id] ?? 0) : 0;
                @endphp

                @if($course)
                    <div class="col-md-6 col-xl-4">
                        <div class="card-soft p-4 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="section-title mb-1">{{ $course->title }}</div>
                                    <div class="small-muted">{{ $course->typeLabel() }}</div>
                                </div>
                                <span class="badge text-bg-success align-self-start">Ativo</span>
                            </div>

                            @if($course->short_description)
                                <p class="small-muted">{{ $course->short_description }}</p>
                            @endif

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="stats-card p-3">
                                        <div class="label">Questões</div>
                                        <div class="value fs-4">{{ $questionCount }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-card p-3">
                                        <div class="label">Acesso até</div>
                                        <div class="fw-bold mt-2">
                                            {{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-grid gap-2">
                                <a href="{{ route('student.courses.show', $course) }}" class="btn btn-primary">Entrar no curso</a>
                                <a href="{{ route('student.courses.study', $course) }}" class="btn btn-outline-primary">Estudar agora</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="section-title mb-3">Cursos disponíveis para assinatura</div>

    @if($availableCourses->isEmpty())
        <div class="card-soft p-4">
            <p class="small-muted mb-0">Nenhum curso público disponível para assinatura no momento.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($availableCourses as $course)
                @php
                    $questionCount = $courseQuestionCounts[$course->id] ?? 0;
                    $cycles = $course->availableBillingCycles();
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="card-soft p-4 h-100 d-flex flex-column">
                        <div class="section-title mb-1">{{ $course->title }}</div>
                        <div class="small-muted mb-3">{{ $course->typeLabel() }}</div>

                        @if($course->short_description)
                            <p class="small-muted">{{ $course->short_description }}</p>
                        @endif

                        <div class="stats-card p-3 mb-3">
                            <div class="label">Questões disponíveis</div>
                            <div class="value fs-4">{{ $questionCount }}</div>
                        </div>

                        <div class="mt-auto">
                            @if(empty($cycles))
                                <button class="btn btn-outline-secondary w-100" disabled>Preço indisponível</button>
                            @else
                                <div class="d-grid gap-2">
                                    @foreach($cycles as $cycle => $label)
                                        <form method="POST" action="{{ route('student.courses.checkout', $course) }}">
                                            @csrf
                                            <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                                            <button class="btn btn-outline-primary w-100">
                                                Assinar {{ $label }} — R$ {{ number_format($course->priceForBillingCycle($cycle), 2, ',', '.') }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
