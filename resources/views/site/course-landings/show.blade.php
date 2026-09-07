@extends('site.site_layout')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('body_class', 'site-page campaign-landing')

@push('head')
<link rel="stylesheet" href="{{ asset('css/course-landing.css') }}">
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->title,
        'description' => $seoDescription,
        'url' => $canonicalUrl,
        'provider' => ['@type' => 'Organization', 'name' => 'Papirar Concursos', 'url' => route('site.home')],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@php
    $ctaText = $course->landing_cta_text ?: ($course->is_trial_available ? 'Começar teste grátis' : 'Começar a estudar');
    $ctaUrl = auth()->check() ? route('student.courses.index') : route('register', ['course' => $course->slug]);
    $bullets = $course->salesBulletsList();
@endphp

<section class="landing-hero">
    <div class="site-container landing-hero-grid">
        <div>
            <span class="landing-kicker">Preparação direcionada · {{ $course->title }}</span>
            <h1>{{ $course->landing_headline ?: 'Prepare-se para ' . $course->title . ' resolvendo questões.' }}</h1>
            <p>{{ $course->landing_subheadline ?: $course->short_description ?: 'Pratique por disciplina e tópico, entenda seus erros e acompanhe sua evolução no Papirar.' }}</p>
            <div class="landing-actions">
                <a href="{{ $ctaUrl }}" class="btn btn-primary js-landing-cta" data-position="hero">{{ $ctaText }}</a>
                @if($demoQuestion)<a href="#experimente" class="btn landing-btn-light">Experimentar uma questão</a>@endif
            </div>
            <div class="landing-proof">
                <span><strong>{{ number_format($totalQuestions, 0, ',', '.') }}</strong> questões</span>
                <span><strong>{{ $subjects->count() }}</strong> disciplinas</span>
                <span><strong>{{ $topics->count() }}</strong> tópicos</span>
                @if($course->is_trial_available)<span><strong>{{ $course->trialDaysForAccess() }}</strong> dias grátis</span>@endif
            </div>
        </div>
        <aside class="landing-course-card">
            @if($course->coverImageUrl())<img src="{{ $course->coverImageUrl() }}" alt="{{ $course->title }}">@endif
            <div>
                <small>Curso completo</small>
                <h2>{{ $course->title }}</h2>
                <strong>{{ $course->bestCommercialPriceLabel() }}</strong>
                <a href="{{ $ctaUrl }}" class="btn btn-primary full js-landing-cta" data-position="hero_card">{{ $ctaText }}</a>
                @if($course->is_trial_available)<p>Teste completo por {{ $course->trialDaysForAccess() }} dias. Sem cobrança automática.</p>@endif
            </div>
        </aside>
    </div>
</section>

<section class="landing-section landing-problem">
    <div class="site-container landing-narrow">
        <span class="landing-kicker">Estudo com direção</span>
        <h2>{{ $course->landing_problem_title ?: 'Estudar muito não basta se você não sabe onde está errando.' }}</h2>
        <p>{{ $course->landing_problem_text ?: 'O Papirar transforma cada resposta em informação para você identificar dificuldades, praticar os assuntos certos e acompanhar sua evolução.' }}</p>
    </div>
</section>

<section class="landing-section">
    <div class="site-container">
        <div class="landing-heading"><span class="landing-kicker">Como funciona</span><h2>Da prática ao diagnóstico</h2></div>
        <div class="landing-steps">
            <article><b>1</b><h3>Escolha a disciplina</h3><p>Entre diretamente no conteúdo que precisa estudar.</p></article>
            <article><b>2</b><h3>Resolva questões</h3><p>Pratique por tópicos do escopo do curso.</p></article>
            <article><b>3</b><h3>Entenda seus erros</h3><p>Confira gabaritos e comentários didáticos.</p></article>
            <article><b>4</b><h3>Acompanhe a evolução</h3><p>Use seu desempenho para direcionar a revisão.</p></article>
        </div>
    </div>
</section>

@if(count($bullets))
<section class="landing-section landing-soft">
    <div class="site-container landing-benefits">
        <div><span class="landing-kicker">O que você recebe</span><h2>Uma preparação organizada para {{ $course->title }}</h2></div>
        <ul>@foreach($bullets as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>
    </div>
</section>
@endif

@if($subjects->isNotEmpty())
<section class="landing-section">
    <div class="site-container">
        <div class="landing-heading"><span class="landing-kicker">Conteúdo real</span><h2>Disciplinas incluídas</h2></div>
        <div class="landing-subjects">
            @foreach($subjects as $subject)
                <a href="{{ route('site.questions.subject', ['subjectSlug' => $subject->slug]) }}">
                    <strong>{{ $subject->name }}</strong><span>Explorar questões →</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($demoQuestion)
<section class="landing-section landing-demo" id="experimente">
    <div class="site-container landing-demo-grid">
        <div><span class="landing-kicker">Experimente agora</span><h2>Responda uma questão real</h2><p>Use o mesmo mecanismo de questões públicas do Papirar.</p></div>
        <article class="landing-question-card">
            <div class="landing-question-meta"><span>{{ $demoQuestion->subject?->name }}</span>@if($demoQuestion->topic)<span>{{ $demoQuestion->topic->name }}</span>@endif</div>
            @if($demoQuestion->exam || $demoQuestion->examBoard)<small><strong>Referência:</strong> {{ $demoQuestion->exam?->title ?: 'Questão de concurso' }}@if($demoQuestion->examBoard) · {{ $demoQuestion->examBoard->name }}@endif</small>@endif
            <div class="landing-statement math-content">{!! $demoQuestion->statement !!}</div>
            <form method="POST" action="{{ route('site.questions.answer', ['subjectSlug' => \App\Support\PublicQuestionUrl::subjectSlug($demoQuestion), 'question' => $demoQuestion->id, 'questionSlug' => \App\Support\PublicQuestionUrl::questionSlug($demoQuestion)]) }}">
                @csrf
                @foreach($demoQuestion->alternatives as $alternative)
                    <label class="landing-alternative math-content"><input type="radio" name="alternative_id" value="{{ $alternative->id }}" required><b>{{ $alternative->letter }}</b><span>{!! $alternative->text !!}</span></label>
                @endforeach
                <button class="btn btn-primary" type="submit">Responder questão</button>
            </form>
        </article>
    </div>
</section>
@endif

<section class="landing-section landing-final">
    <div class="site-container">
        <h2>{{ $course->landing_final_title ?: 'Comece hoje sua preparação para ' . $course->title }}</h2>
        <p>{{ $course->landing_final_text ?: 'Teste a experiência completa do Papirar e descubra onde concentrar seus estudos.' }}</p>
        <a href="{{ $ctaUrl }}" class="btn btn-primary js-landing-cta" data-position="final">{{ $ctaText }}</a>
    </div>
</section>
@endsection

@push('scripts')
<script>window.PapirarCourseLanding = @json(['course_id' => (int) $course->id, 'course_slug' => $course->slug, 'landing_page' => $course->slug]);</script>
<script src="{{ asset('js/course-landing-tracking.js') }}" defer></script>
@endpush
