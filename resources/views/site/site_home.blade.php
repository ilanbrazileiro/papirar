@extends('site.site_layout')

@section('title', 'Papirar Concursos | Escolha seu curso e estude por questões')
@section('meta_description', 'Escolha seu curso e comece a estudar por questões. Prepare-se para concursos militares, policiais e outros concursos com questões por disciplina e tópico, simulados e acompanhamento de desempenho.')
@section('canonical', url('/'))
@section('og_title', 'Papirar Concursos | Escolha seu curso e estude por questões')
@section('og_description', 'Escolha seu objetivo, entre no curso correspondente e estude por questões no Papirar.')

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
@endphp

<section class="home-hero">
    <div class="site-container home-hero-grid">
        <div class="home-hero-copy">
            <span class="home-kicker">Estude por questões</span>
            <h1>Escolha seu curso e <span>comece a estudar por questões.</span></h1>
            <p class="home-hero-text">
                Prepare-se para concursos militares, policiais e outros concursos disponíveis
                no Papirar com questões organizadas por disciplina e tópico, comentários
                explicativos, simulados e acompanhamento de desempenho.
            </p>

            <div class="home-hero-actions">
                @if($publicCourses->isNotEmpty())
                    <a href="#cursos" class="btn btn-primary home-main-cta">Escolher meu curso</a>
                @else
                    <a href="{{ $registerUrl }}" class="btn btn-primary home-main-cta">Começar gratuitamente</a>
                @endif

                <a href="{{ route('site.questions.index') }}" class="btn home-btn-light">Explorar questões grátis</a>
            </div>

            <div class="home-proof-row">
                <span>✓ Questões comentadas</span>
                <span>✓ Estudo por tópico</span>
                <span>✓ Simulados</span>
                <span>✓ Desempenho</span>
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
            <div>
                <span class="home-kicker">Escolha seu curso</span>
                <h2>Para qual concurso você está se preparando?</h2>
            </div>
            <p>
                Escolha o curso correspondente ao seu objetivo e comece por uma preparação
                já organizada com as disciplinas e tópicos do seu concurso.
            </p>
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
                        <div class="home-course-meta">
                            @if($course->corporation)<span>{{ $course->corporation->name }}</span>@endif
                            <span>{{ $course->typeLabel() }}</span>
                        </div>

                        <h3>{{ $course->title }}</h3>
                        <p>{{ $course->short_description ?: $course->commercialHeadline() }}</p>

                        @php($bullets = array_slice($course->salesBulletsList(), 0, 3))
                        @if(count($bullets))
                            <ul class="home-course-bullets">
                                @foreach($bullets as $bullet)<li>{{ $bullet }}</li>@endforeach
                            </ul>
                        @endif

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
        <div class="home-section-heading home-heading-centered">
            <div><span class="home-kicker">Como funciona</span><h2>Escolha seu objetivo. Resolva questões. Entenda seus erros. Evolua.</h2></div>
            <p>O Papirar organiza a preparação dentro do curso escolhido para você gastar menos tempo procurando material e mais tempo estudando.</p>
        </div>

        <div class="home-step-grid">
            <article><span class="home-step-number">01</span><h3>Escolha seu curso</h3><p>Encontre a preparação correspondente ao concurso que você pretende fazer.</p></article>
            <article><span class="home-step-number">02</span><h3>Escolha o que estudar</h3><p>Direcione a sessão por disciplina e tópico dentro do conteúdo do curso.</p></article>
            <article><span class="home-step-number">03</span><h3>Resolva e aprenda</h3><p>Responda questões e use os comentários explicativos para consolidar o conteúdo.</p></article>
            <article><span class="home-step-number">04</span><h3>Acompanhe sua evolução</h3><p>Use desempenho, favoritas e simulados para identificar onde precisa melhorar.</p></article>
        </div>
    </div>
</section>

<section class="home-section home-demo">
    <div class="site-container home-demo-grid">
        <div class="home-demo-copy">
            <span class="home-kicker">Experimente antes de cadastrar</span>
            <h2>Veja algumas das questões disponíveis no Papirar.</h2>
            <p>Consulte gratuitamente enunciados e alternativas. Para responder, conferir o gabarito e acessar a resolução comentada, crie sua conta.</p>
            <a href="{{ route('site.questions.index') }}" class="btn btn-primary">Explorar banco de questões</a>
        </div>

        <div class="home-subjects-panel">
            <div class="home-subjects-header"><strong>Disciplinas em destaque</strong><span>Questões disponíveis</span></div>
            @foreach($featuredSubjects as $subject)
                <a href="{{ route('site.questions.subject', ['subjectSlug' => $subject->slug]) }}" class="home-subject-row">
                    <span>{{ $subject->name }}</span><strong>{{ number_format($subject->public_questions_count, 0, ',', '.') }}</strong>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="home-section home-features" id="diferenciais">
    <div class="site-container">
        <div class="home-section-heading">
            <div><span class="home-kicker">Dentro do seu curso</span><h2>Ferramentas para transformar questões em preparação.</h2></div>
            <p>Depois de escolher o curso, você encontra recursos para praticar, revisar e acompanhar seu desempenho.</p>
        </div>

        <div class="home-feature-grid">
            <article><span class="home-feature-icon">01</span><h3>Questões comentadas</h3><p>Confira a lógica da resposta e use a resolução como parte do estudo.</p></article>
            <article><span class="home-feature-icon">02</span><h3>Estudo por disciplina e tópico</h3><p>Refine o conteúdo dentro do escopo do curso e ataque seus pontos fracos.</p></article>
            <article><span class="home-feature-icon">03</span><h3>Simulados</h3><p>Teste seu desempenho em sessões mais próximas da pressão de uma prova.</p></article>
            <article><span class="home-feature-icon">04</span><h3>Desempenho</h3><p>Visualize seus resultados para decidir onde concentrar as próximas sessões.</p></article>
            <article><span class="home-feature-icon">05</span><h3>Questões favoritas</h3><p>Guarde questões estratégicas e retorne a elas durante a revisão.</p></article>
            <article><span class="home-feature-icon">06</span><h3>Conteúdo direcionado</h3><p>Estude dentro das disciplinas e tópicos definidos para o curso escolhido.</p></article>
        </div>
    </div>
</section>

<section class="home-section home-conversion">
    <div class="site-container home-conversion-box">
        <div>
            <span class="home-kicker">Comece agora</span>
            <h2>Escolha seu curso e transforme questões em rotina de estudo.</h2>
            <p>Encontre sua preparação, crie sua conta e comece a resolver questões dentro do conteúdo do seu objetivo.</p>
            @if($trialDays)<span class="home-trial-note">Há cursos com período de teste de {{ $trialDays }} {{ $trialDays === 1 ? 'dia' : 'dias' }}.</span>@endif
        </div>

        <div class="home-conversion-actions">
            @if($publicCourses->isNotEmpty())
                <a href="#cursos" class="btn btn-primary">Escolher meu curso</a>
            @else
                <a href="{{ $registerUrl }}" class="btn btn-primary">Criar conta gratuita</a>
            @endif
            <a href="{{ route('site.questions.index') }}" class="btn home-btn-light">Ver questões</a>
        </div>
    </div>
</section>

<section class="home-section home-faq">
    <div class="site-container">
        <div class="home-section-heading"><div><span class="home-kicker">Dúvidas frequentes</span><h2>Antes de começar</h2></div></div>
        <div class="home-faq-grid">
            <details><summary>Como começo a estudar no Papirar?</summary><p>Escolha um dos cursos disponíveis, crie sua conta e acesse a preparação correspondente ao seu objetivo.</p></details>
            <details><summary>Posso ver questões sem criar conta?</summary><p>Sim. O banco público permite consultar enunciados e alternativas. O cadastro é solicitado quando você deseja responder e conferir a resolução.</p></details>
            <details><summary>O Papirar organiza questões por tópico?</summary><p>Sim. As questões são classificadas por disciplina e tópico, permitindo concentrar o estudo em conteúdos específicos.</p></details>
            <details><summary>Existem simulados?</summary><p>Sim. Os cursos podem oferecer simulados para complementar as sessões normais de resolução de questões.</p></details>
        </div>
    </div>
</section>
@endsection
