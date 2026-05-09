<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Papirar Concursos | Questões para concursos internos militares')</title>
    <meta name="description" content="@yield('meta_description', 'Papirar Concursos: plataforma de questões para concursos internos militares da PMERJ e CBMERJ, com estudo por concurso previsto, disciplina e tópico.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="theme-color" content="#0B1F3A">

    <link rel="canonical" href="@yield('canonical', '/')">
    <link rel="stylesheet" href="{{ asset('css/site-papirar.css') }}">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Papirar Concursos">
    <meta property="og:title" content="@yield('og_title', 'Papirar Concursos | Questões para concursos internos militares')">
    <meta property="og:description" content="@yield('og_description', 'Estude por concurso previsto, disciplina e tópico com questões direcionadas para PMERJ, CBMERJ e concursos internos militares.')">
    <meta property="og:url" content="@yield('canonical', '/')">
    <meta property="og:image" content="{{ asset('images/papirar-logo-full.png') }}">

    @stack('head')
</head>
<body class="@yield('body_class', 'site-page')">
    @include('site.site_topbar')

    @if (session('success') || session('status') || session('error'))
        <div class="site-alert-wrap">
            @if (session('success'))
                <div class="site-alert site-alert-success">{{ session('success') }}</div>
            @endif
            @if (session('status'))
                <div class="site-alert site-alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="site-alert site-alert-error">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-container footer-grid">
            <div>
                <img src="{{ asset('images/papirar-logo-full.png') }}" alt="Papirar Concursos" class="footer-logo footer-logo-wide">
                <p>Plataforma de questões para concursos internos militares.</p>
            </div>
            <div>
                <strong>Papirar</strong>
                <a href="{{ url('/') }}">Início</a>
                <a href="{{ url('/login') }}">Área do aluno</a>
                <a href="https://www.instagram.com/papirar.concursos" target="_blank" rel="noopener">Instagram</a>
            </div>
            <div>
                <strong>Concursos</strong>
                <span>CHOE PMERJ</span>
                <span>CHOAE CBMERJ</span>
                <span>Concursos internos militares</span>
            </div>
        </div>
        <div class="footer-bottom">© {{ date('Y') }} Papirar Concursos. Todos os direitos reservados.</div>
    </footer>

    @stack('scripts')
</body>
</html>
