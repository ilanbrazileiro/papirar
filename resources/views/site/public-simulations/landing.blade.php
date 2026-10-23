@extends('site.site_layout')
@section('title',\Illuminate\Support\Str::limit($sim->title.' | Simulado grátis | Papirar',70))
@section('meta_description',\Illuminate\Support\Str::limit($sim->description?:'Resolva gratuitamente o simulado '.$sim->title.' e confira o gabarito comentado.',155))
@section('canonical',route('public-simulations.landing',$sim->slug))
@section('og_title',$sim->title.' | Simulado gratuito')
@section('og_description',\Illuminate\Support\Str::limit($sim->description?:'Teste seus conhecimentos gratuitamente no Papirar.',155))
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}?v=3.1">@endpush
@section('content')
<div class="ps-wrap ps-landing">
    <nav class="ps-breadcrumb"><a href="{{ route('public-simulations.index') }}">Simulados gratuitos</a><span aria-hidden="true">/</span><span>{{ $sim->course->title }}</span></nav>
    <section class="ps-hero ps-hero-landing">
        <div class="ps-hero-content">
            <span class="ps-eyebrow ps-eyebrow-light">SIMULADO GRATUITO • GABARITO COMENTADO</span>
            <h1>{{ $sim->title }}</h1>
            <p>{{ $sim->description ?: 'Teste seus conhecimentos, descubra seus pontos de melhoria e avance na preparação para seu concurso.' }}</p>
            <div class="ps-facts">
                <span>✓ {{ $sim->questions()->count() }} questões</span>
                <span>◷ {{ $sim->duration_minutes }} minutos</span>
                <span>✓ Primeiro acesso sem cadastro</span>
            </div>
            <a href="#comecar" class="ps-button ps-button-light">Quero fazer o simulado <span aria-hidden="true">↓</span></a>
        </div>
    </section>
    @if(session('error'))<div class="ps-alert" role="alert">{{ session('error') }}</div>@endif
    <div class="ps-landing-layout">
        <section id="comecar" class="ps-card ps-start-card">
            <span class="ps-eyebrow">COMECE AGORA</span>
            <h2>Descubra como está sua preparação</h2>
            <p>Resolva questões de um concurso realista, veja seu desempenho e confira o <strong>gabarito comentado completo</strong> ao terminar.</p>
            <div class="ps-value-list">
                <div><span class="ps-check">✓</span> Questões com alternativas</div>
                <div><span class="ps-check">✓</span> Resultado com acertos e erros</div>
                <div><span class="ps-check">✓</span> Comentários para aprender com cada questão</div>
            </div>
            @if($existing)
                @if($existing->completed_at)
                    <a class="ps-button ps-button-outline ps-button-wide" href="{{ route('public-simulations.result',$sim->slug) }}">Rever meu resultado →</a>
                    <form method="POST" action="{{ route('public-simulations.start',$sim->slug) }}">
                        @csrf
                        <input type="hidden" name="replay" value="1">
                        <button type="submit" class="ps-button ps-button-primary ps-button-wide">Refazer este simulado →</button>
                    </form>
                    <p class="ps-small">Nova tentativa com as mesmas questões. Seus resultados anteriores são preservados; não consome novo simulado gratuito.</p>
                @else
                    <a class="ps-button ps-button-primary ps-button-wide" href="{{ route('public-simulations.take',$sim->slug) }}">Continuar simulado →</a>
                @endif
            @elseif(!$subscribed && $count >= (auth()->check()?2:1))
                <div class="ps-alert"><strong>Seu limite de simulados gratuitos foi atingido.</strong><p>Continue estudando com uma conta gratuita ou conheça o curso para acesso ilimitado.</p></div>
                @if(auth()->guest())<a class="ps-button ps-button-primary ps-button-wide" href="{{ route('register') }}">Criar conta gratuita <span aria-hidden="true">→</span></a>
                @else<a class="ps-button ps-button-primary ps-button-wide" href="{{ $sim->course->landing_enabled ? route('site.course-landings.show',$sim->course->slug) : route('site.courses.show',$sim->course->slug) }}">Conhecer o curso <span aria-hidden="true">→</span></a>@endif
            @else
                <form method="POST" action="{{ route('public-simulations.start',$sim->slug) }}">@csrf<button type="submit" class="ps-button ps-button-primary ps-button-wide">Começar simulado grátis <span aria-hidden="true">→</span></button></form>
            @endif
            <p class="ps-small">Sem cadastro no primeiro simulado · 2 simulados gratuitos após cadastro, contando o primeiro · Ilimitados para assinantes pagos do curso.</p>
            <button id="ps-share" type="button" class="ps-button ps-button-outline ps-button-wide">Compartilhar este simulado</button>
        </section>
        @include('site.public-simulations.course-card')
    </div>
</div>
@endsection
@push('scripts')
<script>
(()=>{const b=document.getElementById('ps-share');if(!b)return;b.addEventListener('click',async()=>{const url=location.href;try{if(navigator.share){await navigator.share({title:document.title,url});return;}await navigator.clipboard.writeText(url);b.textContent='Link copiado!';}catch(e){if(e.name!=='AbortError')b.textContent='Copie o link na barra de endereço';}});})();
</script>
@endpush
