@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register')
        ? route('register')
        : url('/cadastro');
@endphp

<header class="site-header">
    <div class="site-container header-inner">
        <a class="brand" href="{{ url('/') }}" aria-label="Papirar Concursos">
            <img src="{{ asset('images/papirar-logo-full.png') }}" alt="Papirar Concursos">
        </a>

        <button type="button" class="site-menu-toggle" id="siteMenuToggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="siteMainNav">
            <span></span><span></span><span></span>
        </button>

        <nav class="site-nav" id="siteMainNav" aria-label="Menu principal">
            <a href="{{ url('/') }}#cursos">Cursos</a>
            <a href="{{ route('site.questions.index') }}">Questões</a>
            <a href="{{ url('/') }}#como-funciona">Como funciona</a>
            <a href="{{ url('/') }}#diferenciais">Recursos</a>
            <a href="{{ url('/login') }}">Entrar</a>
            <a class="nav-cta" href="{{ $registerUrl }}">Começar grátis</a>
        </nav>
    </div>
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('siteMenuToggle');
    const nav = document.getElementById('siteMainNav');
    if (!button || !nav) return;
    button.addEventListener('click', function () {
        const isOpen = nav.classList.toggle('is-open');
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            nav.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>
@endpush
