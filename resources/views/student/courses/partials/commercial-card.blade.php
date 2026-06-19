@php
    $access = $access ?? null;
    $questionCount = (int) ($questionCount ?? 0);
    $mode = $mode ?? 'available';
    $cycles = $course->availableBillingCycles();
    $coverUrl = $course->coverImageUrl();
    $bullets = $course->salesBulletsList();
    $badge = $course->sales_badge ?: ($mode === 'active' ? 'Curso ativo' : 'Curso disponível');
@endphp

<div class="course-commercial-card h-100 d-flex flex-column overflow-hidden">
    <div class="course-cover-wrap">
        @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $course->title }}" class="course-cover-img">
        @else
            <div class="course-cover-placeholder">
                <div class="course-cover-logo">P</div>
                <div>
                    <div class="fw-bold">Papirar</div>
                    <div class="small opacity-75">Concursos militares</div>
                </div>
            </div>
        @endif

        <span class="course-badge">{{ $badge }}</span>
    </div>

    <div class="p-4 d-flex flex-column flex-grow-1">
        <div class="small-muted mb-1">{{ $course->typeLabel() }}</div>
        <h3 class="course-card-title mb-2">{{ $course->title }}</h3>
        <p class="course-card-headline mb-3">{{ $course->commercialHeadline() }}</p>

        <div class="course-meta-grid mb-3">
            <div>
                <span class="label">Questões</span>
                <strong>{{ $questionCount }}</strong>
            </div>
            <div>
                <span class="label">Acesso</span>
                @if($access)
                    <strong>{{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</strong>
                @else
                    <strong>{{ $course->trial_days ?: 7 }} dias teste</strong>
                @endif
            </div>
        </div>

        @if($course->target_audience || $course->workload_label)
            <div class="small-muted mb-3">
                @if($course->target_audience)
                    <div><strong>Para:</strong> {{ $course->target_audience }}</div>
                @endif
                @if($course->workload_label)
                    <div><strong>Conteúdo:</strong> {{ $course->workload_label }}</div>
                @endif
            </div>
        @endif

        @if(!empty($bullets))
            <ul class="course-bullets mb-3">
                @foreach(array_slice($bullets, 0, 4) as $bullet)
                    <li>{{ $bullet }}</li>
                @endforeach
            </ul>
        @endif

        @if($mode === 'active')
            <div class="small-muted mb-3">
                Tipo de acesso: <strong>{{ $access?->accessTypeLabel() }}</strong>
            </div>

            <div class="mt-auto d-grid gap-2">
                <a href="{{ route('student.courses.show', $course) }}" class="btn btn-primary">Entrar no curso</a>
                <a href="{{ route('student.courses.study', $course) }}" class="btn btn-outline-primary">Estudar agora</a>
            </div>
        @else
            <div class="course-price-box mb-3">
                <div class="small-muted">A partir de</div>
                <strong>{{ $course->bestCommercialPriceLabel() }}</strong>
                @if($course->guarantee_text)
                    <div class="small-muted mt-1">{{ $course->guarantee_text }}</div>
                @endif
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
                                <button class="btn {{ $loop->first ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                                    Assinar {{ $label }} — R$ {{ number_format($course->priceForBillingCycle($cycle), 2, ',', '.') }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
