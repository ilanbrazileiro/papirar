<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Papirar Concursos | Estude por questões')</title>
    <meta name="description" content="@yield('meta_description', 'Estude por questões para concursos militares e policiais no Papirar.')">

    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="theme-color" content="#0B1F3A">

    <link rel="canonical" href="@yield('canonical', '/')">
    <link rel="stylesheet" href="{{ asset('css/site-papirar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/katex/katex.min.css') }}">

    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Papirar Concursos">
    <meta property="og:title" content="@yield('og_title', 'Papirar Concursos | Questões para concursos internos militares')">
    <meta property="og:description" content="@yield('og_description', 'Estude por concurso previsto, disciplina e tópico com questões direcionadas para PMERJ, CBMERJ e concursos internos militares.')">
    <meta property="og:url" content="@yield('canonical', '/')">
    <meta property="og:image" content="{{ asset('images/papirar-logo-full.png') }}">

    @if(config('services.analytics.search_console_verification'))
        <meta
            name="google-site-verification"
            content="{{ config('services.analytics.search_console_verification') }}"
        >
    @endif

    @include('components.google-analytics')

    @stack('head')
    <link rel="stylesheet" href="{{ asset('css/site-public-ui.css') }}">
</head>
<body class="@yield('body_class', 'site-page')">
    @include('site.site_topbar')

   

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-container footer-grid">
            <div>
                <img src="{{ asset('images/papirar-logo-full.png') }}" alt="Papirar Concursos" class="footer-logo footer-logo-wide">
                <p>Plataforma para estudar por questões em concursos militares e policiais.</p>
            </div>
            <div>
                <strong>Papirar</strong>
                <a href="{{ url('/') }}">Início</a>
                <a href="{{ url('/login') }}">Área do aluno</a>
                <a href="{{ route('site.questions.index') }}">Questões</a>
                <a href="https://www.instagram.com/papirar.concursos" target="_blank" rel="noopener">Instagram</a>
                <a href="{{ route('site.privacy-policy') }}">Política de Privacidade</a>
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

    <script src="{{ asset('js/site-conversion-tracking.js') }}" defer></script>  
    <script src="{{ asset('assets/katex/katex.min.js') }}"></script>
    <script src="{{ asset('assets/katex/contrib/auto-render.min.js') }}"></script>
    <script src="{{ asset('js/papirar-katex.js') }}"></script>
    @stack('scripts')
</body>
</html>
