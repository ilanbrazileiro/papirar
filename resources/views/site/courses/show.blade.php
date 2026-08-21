@extends('site.site_layout')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)

@push('head')
<link rel="stylesheet" href="{{ asset('css/public-course.css') }}">

@php
    $courseSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Course',
                'name' => $course->title,
                'description' => $seoDescription,
                'url' => $canonicalUrl,
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'Papirar Concursos',
                    'url' => route('site.home'),
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Papirar',
                        'item' => route('site.home'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Cursos',
                        'item' => route('site.home') . '#cursos',
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $course->title,
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
        ],
    ];
@endphp

<script type="application/ld+json">{!! json_encode($courseSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register')
        ? route('register')
        : url('/cadastro');

    $loggedInCourseUrl = auth()->check() && \Illuminate\Support\Facades\Route::has('student.courses.index')
        ? route('student.courses.index')
        : $registerUrl;

    $bullets = $course->salesBulletsList();
@endphp

<section class="course-hero-public">
    <div class="site-container course-hero-grid">
        <div class="course-hero-copy">
            <nav class="course-breadcrumb" aria-label="Navegação estrutural">
                <a href="{{ route('site.home') }}">Início</a>
                <span>›</span>
                <a href="{{ route('site.home') }}#cursos">Cursos</a>
                <span>›</span>
                <span>{{ $course->title }}</span>
            </nav>

            <div class="course-tags">
                @if($course->corporation)
                    <span>{{ $course->corporation->name }}</span>
                @endif

                <span>{{ $course->typeLabel() }}</span>

                @if($course->sales_badge)
                    <span class="course-tag-gold">{{ $course->sales_badge }}</span>
                @endif
            </div>

            <h1>{{ $course->sales_headline ?: $course->title }}</h1>

            @if($course->sales_headline && $course->sales_headline !== $course->title)
                <h2 class="course-title-secondary">{{ $course->title }}</h2>
            @endif

            <p class="course-hero-description">
                {{ $course->short_description ?: $course->commercialHeadline() }}
            </p>

            <div class="course-hero-actions">
                <a href="{{ $loggedInCourseUrl }}" class="btn btn-primary">
                    {{ auth()->check() ? 'Acessar cursos' : 'Começar agora' }}
                </a>

                @if($sampleQuestions->isNotEmpty())
                    <a href="#questoes-do-curso" class="btn course-btn-light">
                        Ver questões do curso
                    </a>
                @endif
            </div>

            <div class="course-hero-proof">
                <span>✓ {{ number_format($totalQuestions, 0, ',', '.') }} questões no escopo</span>

                @if($subjects->isNotEmpty())
                    <span>✓ {{ $subjects->count() }} disciplinas</span>
                @endif

                @if($course->is_trial_available)
                    <span>✓ Teste de {{ $course->trialDaysForAccess() }} {{ $course->trialDaysForAccess() === 1 ? 'dia' : 'dias' }}</span>
                @endif
            </div>
        </div>

        <aside class="course-buy-card">
            @if($course->coverImageUrl())
                <img
                    src="{{ $course->coverImageUrl() }}"
                    alt="{{ $course->title }}"
                    class="course-buy-cover"
                >
            @endif

            <div class="course-buy-body">
                <span class="course-buy-label">Acesso ao curso</span>

                <h2>{{ $course->bestCommercialPriceLabel() }}</h2>

                @if($course->price > 0)
                    <div class="course-price-row">
                        <span>Mensal</span>
                        <strong>R$ {{ number_format((float)$course->price, 2, ',', '.') }}</strong>
                    </div>
                @endif

                @if($course->hasQuarterlyPrice())
                    <div class="course-price-row">
                        <span>Trimestral</span>
                        <strong>R$ {{ number_format((float)$course->quarterly_price, 2, ',', '.') }}</strong>
                    </div>
                @endif

                @if($course->hasSemiannualPrice())
                    <div class="course-price-row">
                        <span>Semestral</span>
                        <strong>R$ {{ number_format((float)$course->semiannual_price, 2, ',', '.') }}</strong>
                    </div>
                @endif

                <a href="{{ $loggedInCourseUrl }}" class="btn btn-primary full">
                    {{ auth()->check() ? 'Ir para meus cursos' : 'Criar conta e começar' }}
                </a>

                @if($course->is_trial_available)
                    <small class="course-trial-text">
                        Período de teste disponível por {{ $course->trialDaysForAccess() }}
                        {{ $course->trialDaysForAccess() === 1 ? 'dia' : 'dias' }}.
                    </small>
                @endif

                @if($course->guarantee_text)
                    <p class="course-guarantee">{{ $course->guarantee_text }}</p>
                @endif
            </div>
        </aside>
    </div>
</section>

@if(count($bullets) || $course->description || $course->target_audience)
<section class="course-section">
    <div class="site-container course-content-grid">
        <div>
            <span class="course-kicker">Preparação direcionada</span>
            <h2>O que você encontra neste curso</h2>

            @if($course->description)
                <div class="course-rich-text">{!! $course->description !!}</div>
            @elseif($course->target_audience)
                <p class="course-lead">{{ $course->target_audience }}</p>
            @endif
        </div>

        @if(count($bullets))
            <div class="course-benefit-card">
                <strong>Principais benefícios</strong>

                <ul>
                    @foreach($bullets as $bullet)
                        <li>{{ $bullet }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>
@endif

@if($subjects->isNotEmpty())
<section class="course-section course-section-light">
    <div class="site-container">
        <div class="course-section-heading">
            <span class="course-kicker">Conteúdo</span>
            <h2>Disciplinas do curso</h2>
            <p>
                Veja as principais disciplinas incluídas na preparação e acesse
                o banco público de questões de cada área.
            </p>
        </div>

        <div class="course-subject-grid">
            @foreach($subjects as $subject)
                <a
                    href="{{ route('site.questions.subject', ['subjectSlug' => $subject->slug]) }}"
                    class="course-subject-card"
                >
                    <span>{{ $subject->name }}</span>
                    <strong>Ver questões →</strong>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($topics->isNotEmpty())
<section class="course-section">
    <div class="site-container">
        <div class="course-section-heading">
            <span class="course-kicker">Tópicos</span>
            <h2>Conteúdos trabalhados</h2>
        </div>

        <div class="course-topic-cloud">
            @foreach($topics as $topic)
                @if($topic->subject)
                    <a
                        href="{{ route('site.questions.topic', [
                            'subjectSlug' => $topic->subject->slug,
                            'topicSlug' => $topic->slug,
                        ]) }}"
                    >
                        {{ $topic->name }}
                    </a>
                @else
                    <span>{{ $topic->name }}</span>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

@if($sampleQuestions->isNotEmpty())
<section class="course-section course-section-dark" id="questoes-do-curso">
    <div class="site-container">
        <div class="course-section-heading">
            <span class="course-kicker">Experimente</span>
            <h2>Questões que fazem parte deste escopo</h2>
            <p>
                Consulte enunciado e alternativas gratuitamente. Para responder,
                conferir o gabarito e acessar a resolução comentada, crie sua conta.
            </p>
        </div>

        <div class="course-question-grid">
            @foreach($sampleQuestions as $question)
                <article class="course-question-card">
                    <div class="course-question-meta">
                        @if($question['subject'])
                            <span>{{ $question['subject'] }}</span>
                        @endif

                        @if($question['topic'])
                            <span>{{ $question['topic'] }}</span>
                        @endif

                        @if($question['board'])
                            <span>{{ $question['board'] }}</span>
                        @endif

                        @if($question['year'])
                            <span>{{ $question['year'] }}</span>
                        @endif
                    </div>

                    <p>{{ $question['statement'] }}</p>

                    <a href="{{ $question['url'] }}">Ver questão e alternativas →</a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="course-section course-final-cta">
    <div class="site-container course-final-box">
        <div>
            <span class="course-kicker">Estude por questões</span>
            <h2>Comece sua preparação para {{ $course->title }}.</h2>
            <p>
                Entre no curso, organize a prática por disciplina e tópico
                e acompanhe sua evolução dentro do Papirar.
            </p>
        </div>

        <a href="{{ $loggedInCourseUrl }}" class="btn btn-primary">
            {{ auth()->check() ? 'Acessar meus cursos' : 'Criar conta gratuita' }}
        </a>
    </div>
</section>
@endsection
