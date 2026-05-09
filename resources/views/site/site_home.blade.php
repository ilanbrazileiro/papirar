@extends('site.site_layout')

@section('title')
Papirar Concursos | Questões para CHOE PMERJ, CBMERJ e concursos internos militares
@endsection

@section('meta_description')
Estude para CHOE PMERJ, CBMERJ e concursos internos militares com questões direcionadas por concurso previsto, disciplina e tópico. Teste o Papirar por 7 dias.
@endsection

@section('canonical')
{{ url('/') }}
@endsection

@section('og_title')
Papirar Concursos | Questões para concursos internos militares
@endsection

@section('og_description')
Escolha seu concurso, marque as disciplinas e treine com questões direcionadas para PMERJ, CBMERJ e concursos internos militares.
@endsection

@push('head')
@php
    $papirarSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => 'Papirar Concursos',
        'url' => url('/'),
        'description' => 'Plataforma de questões para concursos internos militares da PMERJ e CBMERJ.',
        'sameAs' => ['https://www.instagram.com/papirar.concursos'],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($papirarSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro');
@endphp

<section class="hero">
    <div class="site-container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Concursos internos militares</span>
            <h1>Estude para CHOE PMERJ, CBMERJ e concursos internos com questões direcionadas.</h1>
            <p class="hero-text">O Papirar organiza seu estudo por concurso previsto, disciplina e tópico para você treinar com foco no que realmente pode cair.</p>

            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ $registerUrl }}">Testar grátis por 7 dias</a>
                <a class="btn btn-secondary" href="{{ url('/login') }}">Entrar na área do aluno</a>
            </div>

            <div class="trust-row">
                <span>PMERJ e CBMERJ</span>
                <span>Questões por disciplina</span>
                <span>Estudo por concurso</span>
            </div>
        </div>

        <div class="hero-card" aria-label="Prévia da plataforma Papirar">
            <div class="browser-dots"><span></span><span></span><span></span></div>
            <img src="{{ asset('images/papirar-sistema-preview.png') }}" alt="Tela do sistema Papirar com área de estudos">
        </div>
    </div>
</section>

<section class="stats-strip">
    <div class="site-container stats-grid">
        <div><strong>+1000</strong><span>questões iniciais e em expansão</span></div>
        <div><strong>100%</strong><span>foco em concursos internos militares</span></div>
        <div><strong>7 dias</strong><span>de teste gratuito ao criar conta</span></div>
    </div>
</section>

<section class="section" id="como-funciona">
    <div class="site-container">
        <div class="section-head">
            <span class="eyebrow">Como funciona</span>
            <h2>Você escolhe o concurso. O Papirar monta o estudo.</h2>
            <p>O fluxo foi pensado para o militar que quer estudar com objetividade, sem perder tempo montando filtros técnicos.</p>
        </div>

        <div class="feature-grid three">
            <article>
                <span class="number">1</span>
                <h3>Escolha a corporação</h3>
                <p>Selecione PMERJ, CBMERJ ou a corporação disponível para o seu objetivo.</p>
            </article>
            <article>
                <span class="number">2</span>
                <h3>Escolha o concurso</h3>
                <p>Use o estudo por concurso previsto ou publicado, com disciplinas vinculadas ao edital.</p>
            </article>
            <article>
                <span class="number">3</span>
                <h3>Resolva questões</h3>
                <p>Treine questões compatíveis por disciplina e tópico, com reaproveitamento inteligente.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section-dark" id="diferenciais">
    <div class="site-container split-grid">
        <div>
            <span class="eyebrow gold">Diferencial Papirar</span>
            <h2>Não é uma plataforma genérica. É estudo focado em concursos internos militares.</h2>
            <p>O Papirar nasce para atender provas internas e conteúdos que muitas plataformas amplas não tratam com a profundidade necessária.</p>
        </div>
        <div class="check-list">
            <p>✓ Filtro por concurso previsto e disciplinas relacionadas.</p>
            <p>✓ Reaproveitamento de questões por disciplina e tópico.</p>
            <p>✓ Separação de matérias específicas por corporação.</p>
            <p>✓ Comentários explicativos para reforçar o aprendizado.</p>
            <p>✓ Teste grátis de 7 dias no cadastro.</p>
        </div>
    </div>
</section>

<section class="section" id="concursos">
    <div class="site-container">
        <div class="section-head">
            <span class="eyebrow">Conteúdo direcionado</span>
            <h2>Preparação para PMERJ, CBMERJ e concursos internos.</h2>
            <p>O banco de questões cresce continuamente, priorizando concursos internos e disciplinas estratégicas.</p>
        </div>
        <div class="exam-grid">
            <article><strong>CHOE PMERJ</strong><span>Questões por disciplinas cobradas no concurso.</span></article>
            <article><strong>CHOAE CBMERJ</strong><span>Conteúdo direcionado para o oficialato administrativo e especialista.</span></article>
            <article><strong>Português e Matemática</strong><span>Base geral reaproveitável por tópico.</span></article>
            <article><strong>Legislação Militar</strong><span>Separada por corporação para evitar estudo errado.</span></article>
        </div>
    </div>
</section>

<section class="section section-light" id="planos">
    <div class="site-container">
        <div class="section-head">
            <span class="eyebrow">Planos</span>
            <h2>Comece com 7 dias grátis. Depois escolha o melhor acesso.</h2>
            <p>Após o período de teste, mantenha o acesso ativo com um dos planos disponíveis.</p>
        </div>

        <div class="pricing-grid">
            <article class="price-card">
                <h3>Mensal</h3>
                <p class="plan-slug">mensal • 30 dias</p>
                <strong>R$ 29,90</strong>
                <a class="btn btn-primary full" href="{{ $registerUrl }}">Testar grátis</a>
            </article>
            <article class="price-card featured">
                <span class="badge">Mais escolhido</span>
                <h3>Trimestral</h3>
                <p class="plan-slug">trimestral</p>
                <strong>R$ 84,90</strong>
                <a class="btn btn-primary full" href="{{ $registerUrl }}">Testar grátis</a>
            </article>
            <article class="price-card">
                <h3>Semestral</h3>
                <p class="plan-slug">semestral</p>
                <strong>R$ 165,90</strong>
                <a class="btn btn-primary full" href="{{ $registerUrl }}">Testar grátis</a>
            </article>
        </div>
    </div>
</section>

<section class="section final-cta">
    <div class="site-container center">
        <h2>Comece a estudar com mais direção.</h2>
        <p>Crie sua conta e teste o Papirar por 7 dias antes de assinar.</p>
        <a class="btn btn-primary" href="{{ $registerUrl }}">Criar conta gratuita</a>
    </div>
</section>
@endsection
