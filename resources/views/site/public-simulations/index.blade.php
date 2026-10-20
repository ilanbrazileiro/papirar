@extends('site.site_layout')
@section('title','Simulados gratuitos para concursos | Papirar')
@section('meta_description','Resolva um simulado gratuito de concursos militares sem cadastro e veja o gabarito comentado completo.')
@section('canonical',route('public-simulations.index'))
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}">@endpush
@section('content')
<div class="ps-wrap"><header class="ps-hero"><h1>Simulados gratuitos para concursos militares</h1><p>Teste seus conhecimentos com questões de concursos. Seu primeiro simulado é livre, sem cadastro.</p></header>
<div class="ps-grid">@forelse($simulations as $sim)
<article class="ps-card"><div class="ps-subtle">{{ $sim->course?->title }} · {{ $sim->questions_count }} questões · {{ $sim->duration_minutes }} minutos</div><h2>{{ $sim->title }}</h2><p>{{ \Illuminate\Support\Str::limit($sim->description,155) }}</p><a class="ps-button" href="{{ route('public-simulations.landing',$sim->slug) }}">Ver simulado</a></article>
@empty<p>Nenhum simulado público disponível por enquanto.</p>@endforelse</div>{{ $simulations->links() }}</div>
@endsection
