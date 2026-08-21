@extends('site.site_layout')

@section('title', 'Papirar Concursos | Estude por questões para concursos militares e policiais')
@section('meta_description', 'Estude por questões para concursos militares e policiais. Resolva questões organizadas por concurso, disciplina e tópico, acompanhe seu desempenho e faça simulados no Papirar.')
@section('canonical', url('/'))
@section('og_title', 'Papirar Concursos | Estude por questões')
@section('og_description', 'Questões organizadas por concurso, disciplina e tópico para uma preparação mais objetiva.')

@push('head')
<link rel="stylesheet" href="{{ asset('css/site-home-v2.css') }}">
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'Organization', '@id' => url('/') . '#organization', 'name' => 'Papirar Concursos', 'url' => url('/'), 'logo' => asset('images/papirar-logo-full.png'), 'sameAs' => ['https://www.instagram.com/papirar.concursos']],
            ['@type' => 'WebSite', '@id' => url('/') . '#website', 'url' => url('/'), 'name' => 'Papirar Concursos', 'publisher' => ['@id' => url('/') . '#organization'], 'inLanguage' => 'pt-BR'],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro');
    $coursesUrl = auth()->check() && \Illuminate\Support\Facades\Route::has('student.courses.index') ? route('student.courses.index') : $registerUrl;
@endphp

<section class="home-hero">
    <div class="site-container home-hero-grid">
        <div class="home-hero-copy">
            <span class="home-kicker">Estude por questões</span>
            <h1>Prepare-se para concursos militares e policiais <span>resolvendo o que realmente importa.</span></h1>
            <p class="home-hero-text">Questões organizadas por concurso, disciplina e tópico. Treine, confira comentários explicativos, acompanhe seu desempenho e faça simulados em uma plataforma feita para estudo objetivo.</p>
            <div class="home-hero-actions">
                <a href="{{ $registerUrl }}" class="btn btn-primary home-main-cta">Começar gratuitamente</a>
                <a href="{{ route('site.questions.index') }}" class="btn home-btn-light">Explorar questões</a>
            </div>
            <div class="home-proof-row">
                <span>✓ Questões comentadas</span><span>✓ Estudo por tópico</span><span>✓ Simulados</span><span>✓ Desempenho</span>
            </div>
        </div>
        <div class="home-platform-preview">
            <div class="preview-window">
                <div class="preview-bar"><span></span><span></span><span></span><small>Área de estudos Papirar</small></div>
                <img src="{{ asset('images/papirar-sistema-preview.png') }}" alt="Área de estudos da plataforma Papirar" width="800" height="500" fetchpriority="high">
            </div>
            <div class="preview-floating preview-floating-top"><strong>{{ number_format($totalQuestions, 0, ',', '.') }}+</strong><span>questões disponíveis</span></div>
            <div class="preview-floating preview-floating-bottom"><strong>{{ $totalSubjects }}</strong><span>disciplinas com questões</span></div>
        </div>
    </div>
</section>

<section class="home-trust-strip">
    <div class="site-container home-trust-grid">
        <div><strong>{{ number_format($totalQuestions, 0, ',', '.') }}+</strong><span>questões públicas no banco</span></div>
        <div><strong>{{ $totalSubjects }}</strong><span>disciplinas para praticar</span></div>
        <div><strong>{{ $publicCourses->count() }}</strong><span>cursos públicos disponíveis</span></div>
        <div><strong>100%</strong><span>foco em estudar por questões</span></div>
    </div>
</section>

@if($publicCourses->isNotEmpty())
<section class="home-section home-courses" id="cursos">
    <div class="site-container">
        <div class="home-section-heading">
            <div><span class="home-kicker">Escolha seu objetivo</span><h2>Cursos preparados para concursos específicos</h2></div>
            <p>Entre em um curso já organizado para o seu concurso e concentre o estudo nas disciplinas e tópicos que fazem parte da preparação.</p>
        </div>
        <div class="home-course-grid">
            @foreach($publicCourses as $course)
                <article class="home-course-card">
                    <div class="home-course-cover">
                        @if($course->coverImageUrl())
                            <img src="{{ $course->coverImageUrl() }}" alt="{{ $course->title }}" loading="lazy">
                        @else
                            <div class="home-course-cover-placeholder"><img src="{{ asset('images/papirar-logo-icon.png') }}" alt="" loading="lazy"></div>
                        @endif
                        @if($course->sales_badge)<span class="home-course-badge">{{ $course->sales_badge }}</span>@endif
                    </div>
                    <div class="home-course-body">
                        <div class="home-course-meta">@if($course->corporation)<span>{{ $course->corporation->name }}</span>@endif<span>{{ $course->typeLabel() }}</span></div>
                        <h3>{{ $course->title }}</h3>
                        <p>{{ $course->short_description ?: $course->commercialHeadline() }}</p>
                        @php($bullets = array_slice($course->salesBulletsList(), 0, 3))
                        @if(count($bullets))<ul class="home-course-bullets">@foreach($bullets as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>@endif
                        <div class="home-course-footer">
                            <div><small>A partir de</small><strong>{{ $course->bestCommercialPriceLabel() }}</strong></div>
                            <a href="{{ route('site.courses.show', ['slug' => $course->slug]) }}" class="home-card-link">Conhecer curso →</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="home-section home-how" id="como-funciona">
    <div class="site-container">
        <div class="home-section-heading home-heading-centered"><div><span class="home-kicker">Como funciona</span><h2>Um ciclo de estudo simples: resolver, entender e evoluir.</h2></div><p>O Papirar reduz o tempo gasto organizando material e transforma questões em uma rotina de preparação mensurável.</p></div>
        <div class="home-step-grid">
            <article><span class="home-step-number">01</span><h3>Escolha o curso</h3><p>Entre na preparação correspondente ao concurso que você está buscando.</p></article>
            <article><span class="home-step-number">02</span><h3>Filtre o estudo</h3><p>Direcione por disciplina e tópico para atacar exatamente o conteúdo desejado.</p></article>
            <article><span class="home-step-number">03</span><h3>Resolva e aprenda</h3><p>Responda questões e use os comentários explicativos para consolidar o conteúdo.</p></article>
            <article><span class="home-step-number">04</span><h3>Acompanhe a evolução</h3><p>Use desempenho, favoritas e simulados para identificar pontos fortes e fracos.</p></article>
        </div>
    </div>
</section>

<section class="home-section home-demo">
    <div class="site-container home-demo-grid">
        <div class="home-demo-copy"><span class="home-kicker">Experimente antes de cadastrar</span><h2>Veja as questões que já fazem parte do Papirar.</h2><p>Nosso banco público permite consultar enunciado e alternativas. Para responder, conferir o gabarito e acessar a resolução comentada, basta criar sua conta.</p><a href="{{ route('site.questions.index') }}" class="btn btn-primary">Explorar banco de questões</a></div>
        <div class="home-subjects-panel">
            <div class="home-subjects-header"><strong>Disciplinas em destaque</strong><span>Questões disponíveis</span></div>
            @foreach($featuredSubjects as $subject)
                <a href="{{ route('site.questions.subject', ['subjectSlug' => $subject->slug]) }}" class="home-subject-row"><span>{{ $subject->name }}</span><strong>{{ number_format($subject->public_questions_count, 0, ',', '.') }}</strong></a>
            @endforeach
        </div>
    </div>
</section>

<section class="home-section home-features" id="diferenciais">
    <div class="site-container">
        <div class="home-section-heading"><div><span class="home-kicker">Dentro da plataforma</span><h2>Mais do que um banco de questões.</h2></div><p>Recursos construídos para transformar resolução de questões em preparação contínua.</p></div>
        <div class="home-feature-grid">
            <article><span class="home-feature-icon">01</span><h3>Questões comentadas</h3><p>Confira a lógica da resposta e use a resolução como parte do estudo.</p></article>
            <article><span class="home-feature-icon">02</span><h3>Estudo por disciplina e tópico</h3><p>Refine o conteúdo e monte sessões alinhadas àquilo que você precisa treinar.</p></article>
            <article><span class="home-feature-icon">03</span><h3>Simulados</h3><p>Teste desempenho em sessões mais próximas da pressão de uma prova.</p></article>
            <article><span class="home-feature-icon">04</span><h3>Desempenho</h3><p>Visualize resultados para decidir onde concentrar as próximas sessões.</p></article>
            <article><span class="home-feature-icon">05</span><h3>Questões favoritas</h3><p>Guarde questões estratégicas e retorne a elas durante a revisão.</p></article>
            <article><span class="home-feature-icon">06</span><h3>Cursos direcionados</h3><p>Estude dentro do escopo definido para cada curso e concurso disponível.</p></article>
        </div>
    </div>
</section>

<section class="home-section home-conversion">
    <div class="site-container home-conversion-box">
        <div><span class="home-kicker">Comece agora</span><h2>Troque estudo disperso por uma rotina baseada em questões.</h2><p>Crie sua conta e veja como o Papirar organiza a preparação para você começar a resolver imediatamente.</p>@if($trialDays)<span class="home-trial-note">Há cursos com período de teste de {{ $trialDays }} {{ $trialDays === 1 ? 'dia' : 'dias' }}.</span>@endif</div>
        <div class="home-conversion-actions"><a href="{{ $registerUrl }}" class="btn btn-primary">Criar conta gratuita</a><a href="{{ route('site.questions.index') }}" class="btn home-btn-light">Ver questões</a></div>
    </div>
</section>

<section class="home-section home-faq">
    <div class="site-container">
        <div class="home-section-heading"><div><span class="home-kicker">Dúvidas frequentes</span><h2>Antes de começar</h2></div></div>
        <div class="home-faq-grid">
            <details><summary>Posso ver questões sem criar conta?</summary><p>Sim. O banco público permite consultar enunciados e alternativas. O cadastro é solicitado quando você deseja responder e conferir a resolução.</p></details>
            <details><summary>O Papirar organiza questões por tópico?</summary><p>Sim. As questões são classificadas por disciplina e tópico, permitindo concentrar o estudo em conteúdos específicos.</p></details>
            <details><summary>Existem simulados?</summary><p>Sim. Os cursos podem oferecer simulados para complementar as sessões normais de resolução de questões.</p></details>
            <details><summary>Como sei quais cursos estão disponíveis?</summary><p>Os cursos públicos ativos são exibidos nesta página e na área de cursos após o cadastro.</p></details>
        </div>
    </div>
</section>
@endsection
