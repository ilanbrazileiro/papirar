@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro');
@endphp

<header class="site-header">
    <div class="site-container header-inner">
        <a class="brand" href="{{ url('/') }}" aria-label="Papirar Concursos">
            <img src="{{ asset('images/papirar-logo-full.png') }}" alt="Papirar Concursos">
        </a>

        <nav class="site-nav" aria-label="Menu principal">
            <a href="{{ url('/') }}#como-funciona">Como funciona</a>
            <a href="{{ url('/') }}#concursos">Concursos</a>
            <a href="{{ url('/') }}#diferenciais">Diferenciais</a>
            <a href="{{ url('/') }}#planos">Planos</a>
            <a href="{{ url('/login') }}">Entrar</a>
            <a class="nav-cta" href="{{ $registerUrl }}">Testar grátis</a>
        </nav>
    </div>
</header>
