@extends('site.site_layout')
@section('title',\Illuminate\Support\Str::limit($sim->title.' | Simulado grátis | Papirar',70))
@section('meta_description',\Illuminate\Support\Str::limit($sim->description?:'Resolva gratuitamente o simulado '.$sim->title.' e confira o gabarito comentado.',155))
@section('canonical',route('public-simulations.landing',$sim->slug))
@section('og_title',$sim->title.' | Simulado gratuito')
@section('og_description',\Illuminate\Support\Str::limit($sim->description?:'Teste seus conhecimentos gratuitamente no Papirar.',155))
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}">@endpush
@section('content')
<div class="ps-wrap"><nav><a href="{{ route('public-simulations.index') }}">Simulados gratuitos</a> / {{ $sim->title }}</nav>
<header class="ps-hero"><div>SIMULADO COMPARTILHÁVEL</div><h1>{{ $sim->title }}</h1><p>{{ $sim->description }}</p><p>{{ $sim->course->title }} · {{ $sim->duration_minutes }} minutos</p></header>
@if(session('error'))<div class="ps-card" role="alert"><strong>{{ session('error') }}</strong></div>@endif
<div class="ps-card"><h2>Faça seu simulado</h2><p>Receba sua pontuação e o gabarito comentado completo. O primeiro simulado não exige cadastro.</p>
@if($existing)
<a class="ps-button" href="{{ route($existing->completed_at?'public-simulations.result':'public-simulations.take',$sim->slug) }}">{{ $existing->completed_at?'Ver meu resultado':'Continuar simulado' }}</a>
@elseif(!$subscribed&&$count>= (auth()->check()?2:1))
<p><strong>Seu limite de simulados gratuitos foi atingido.</strong></p>
@if(auth()->guest())<a class="ps-button" href="{{ route('register') }}">Criar conta gratuita para fazer outro</a>
@else<a class="ps-button" href="{{ $sim->course->landing_enabled ? route('site.course-landings.show',$sim->course->slug) : route('site.courses.show',$sim->course->slug) }}">Conhecer os planos do curso</a>@endif
@else<form method="POST" action="{{ route('public-simulations.start',$sim->slug) }}">@csrf<button type="submit" class="ps-button">Começar gratuitamente</button></form>@endif
<p class="ps-subtle">Visitantes: 1 simulado · Cadastro gratuito: 2 no total · Assinantes pagos do curso: ilimitados.</p>
<button type="button" class="ps-button" style="background:#e9f1fc;color:#164b85!important" onclick="navigator.share?navigator.share({title:document.title,url:location.href}).catch(()=>{}):navigator.clipboard.writeText(location.href).then(()=>alert('Link copiado'))">Compartilhar este simulado</button>
</div></div>
@endsection
