@once
    @push('styles')
    <style>
        .adaptive-card{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:24px;align-items:center;padding:24px;border:1px solid #c7d9ef;border-radius:23px;background:linear-gradient(135deg,#eef6ff,#fff 62%,#fffbeb);box-shadow:0 14px 36px rgba(15,35,68,.08)}
        .adaptive-kicker{color:#173b72;font-size:.75rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.adaptive-card h2{margin:5px 0;color:#0f2344;font-size:clamp(1.35rem,3vw,1.9rem);font-weight:950}.adaptive-reason{color:#526174}.adaptive-metrics{display:flex;flex-wrap:wrap;gap:8px;margin-top:13px}.adaptive-metrics span{padding:8px 10px;border:1px solid #d9e5f3;border-radius:12px;background:#fff;color:#173b72;font-size:.8rem;font-weight:800}.adaptive-action{min-width:230px}.adaptive-action .btn{width:100%;padding:13px 18px;font-weight:900}
        @media(max-width:767.98px){.adaptive-card{grid-template-columns:1fr;padding:20px}.adaptive-action{min-width:0}}
    </style>
    @endpush
@endonce

<section class="adaptive-card mb-4" data-adaptive-recommendation data-course-id="{{ $adaptiveRecommendation['course']->id }}" data-subject-id="{{ $adaptiveRecommendation['subject_id'] }}" data-topic-id="{{ $adaptiveRecommendation['topic_id'] }}" data-accuracy="{{ $adaptiveRecommendation['accuracy'] }}">
    <div>
        <div class="adaptive-kicker">Recomendação para você</div>
        <h2>Reforce {{ $adaptiveRecommendation['topic'] }}</h2>
        <div class="fw-bold text-primary">{{ $adaptiveRecommendation['course']->title }} · {{ $adaptiveRecommendation['subject'] }}</div>
        <div class="adaptive-reason mt-1">Indicamos esta revisão porque seu aproveitamento neste tópico está abaixo de 70%.</div>
        <div class="adaptive-metrics"><span>{{ $adaptiveRecommendation['answered'] }} respondidas</span><span>{{ $adaptiveRecommendation['wrong'] }} erros pendentes</span><span>{{ number_format($adaptiveRecommendation['accuracy'],1,',','.') }}% de acertos</span></div>
    </div>
    <div class="adaptive-action">
        <form method="POST" action="{{ route('student.course-study.start',$adaptiveRecommendation['course']) }}" data-adaptive-start>
            @csrf
            <input type="hidden" name="subject_ids[]" value="{{ $adaptiveRecommendation['subject_id'] }}">
            <input type="hidden" name="topic_ids[]" value="{{ $adaptiveRecommendation['topic_id'] }}">
            <input type="hidden" name="quantity" value="{{ $adaptiveRecommendation['quantity'] }}">
            <input type="hidden" name="mode" value="{{ $adaptiveRecommendation['mode'] }}">
            <input type="hidden" name="adaptive_recommendation" value="1">
            <button class="btn btn-primary">REVISAR {{ $adaptiveRecommendation['quantity'] }} {{ $adaptiveRecommendation['quantity'] === 1 ? 'QUESTÃO' : 'QUESTÕES' }} →</button>
        </form>
        <div class="small-muted text-center mt-2">A recomendação muda conforme sua evolução.</div>
    </div>
</section>
