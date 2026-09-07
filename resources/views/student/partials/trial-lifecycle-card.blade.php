@php
    $course = $lifecycle['course'];
    $metrics = $lifecycle['metrics'];
    $cycles = $course->availableBillingCycles();
@endphp

@once
    @push('styles')
    <style>
        .trial-life{position:relative;overflow:hidden;padding:clamp(22px,4vw,34px);border-radius:24px;background:linear-gradient(135deg,#0f2344,#173b72 68%,#24589a);color:#fff;box-shadow:0 20px 48px rgba(15,35,68,.2)}
        .trial-life:after{content:'';position:absolute;width:220px;height:220px;right:-90px;bottom:-130px;border-radius:50%;background:rgba(244,197,66,.25)}
        .trial-life>*{position:relative;z-index:1}.trial-life-grid{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:24px;align-items:center}.trial-life-kicker{color:#f4c542;font-size:.76rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.trial-life h2{margin:5px 0 7px;font-size:clamp(1.35rem,3vw,2rem);font-weight:950}.trial-life-course{color:#dbe7f7;font-weight:800}.trial-life-stats{display:flex;flex-wrap:wrap;gap:9px;margin-top:16px}.trial-life-stat{padding:9px 12px;border:1px solid rgba(255,255,255,.16);border-radius:13px;background:rgba(255,255,255,.09);font-size:.82rem}.trial-life-actions{display:grid;gap:9px;min-width:245px}.trial-life-actions .btn{font-weight:900}.trial-life-note{color:#cbd8ea;font-size:.76rem;text-align:center}
        @media(max-width:767.98px){.trial-life-grid{grid-template-columns:1fr}.trial-life-actions{min-width:0}.trial-life-actions .btn{width:100%}}
    </style>
    @endpush
@endonce

<section class="trial-life mb-4" data-trial-lifecycle data-stage="{{ $lifecycle['stage'] }}" data-course-id="{{ $course->id }}" data-days-remaining="{{ $lifecycle['daysRemaining'] }}" data-answers="{{ $metrics['answers'] }}">
    <div class="trial-life-grid">
        <div>
            <div class="trial-life-kicker">{{ $lifecycle['eyebrow'] }}</div>
            <h2>{{ $lifecycle['title'] }}</h2>
            <div class="trial-life-course">{{ $course->title }}</div>
            @if($metrics['answers'] > 0)
                <div class="trial-life-stats">
                    <span class="trial-life-stat"><strong>{{ $metrics['answers'] }}</strong> questões respondidas</span>
                    <span class="trial-life-stat"><strong>{{ number_format($metrics['accuracy'],1,',','.') }}%</strong> de acertos</span>
                    <span class="trial-life-stat"><strong>{{ $metrics['subjects'] }}</strong> {{ $metrics['subjects'] === 1 ? 'disciplina' : 'disciplinas' }}</span>
                </div>
            @else
                <p class="mb-0 mt-2 text-white-50">Resolva suas primeiras questões e veja seu desempenho aparecer aqui.</p>
            @endif
        </div>
        <div class="trial-life-actions">
            @if($lifecycle['stage'] === 'not_started')
                <a href="{{ route('student.courses.study',$course) }}" class="btn btn-warning" data-trial-action="study">COMEÇAR A ESTUDAR →</a>
            @endif
            @foreach($cycles as $cycle => $label)
                <form method="POST" action="{{ route('student.courses.checkout',$course) }}" data-trial-action="checkout" data-billing-cycle="{{ $cycle }}">
                    @csrf
                    <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                    <button class="btn {{ $loop->first ? 'btn-warning' : 'btn-outline-light' }} w-100">Assinar {{ $label }} · R$ {{ number_format($course->priceForBillingCycle($cycle),2,',','.') }}</button>
                </form>
            @endforeach
            @if(empty($cycles))<a href="{{ route('student.subscriptions.index') }}" class="btn btn-warning">VER OPÇÕES DE ASSINATURA</a>@endif
            <div class="trial-life-note">Continue com questões, comentários, simulados e desempenho.</div>
        </div>
    </div>
</section>
