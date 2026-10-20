@extends('site.site_layout')
@section('title','PDFs comentados para concursos | Papirar')
@section('meta_description','Explore materiais comentados organizados por curso e assunto. Consulte a descrição e a prévia antes de acessar o arquivo completo.')
@section('canonical',route('library.materials.index'))
@push('head')<link rel="stylesheet" href="{{ asset('css/library-refresh.css') }}">@endpush
@section('content')
<div class="pm-lib">
<nav aria-label="Navegação"><a href="{{ route('site.home') }}">Início</a> / PDFs comentados para concursos</nav>
<header class="pm-lib-hero"><div class="pm-lib-eyebrow">Papirar Concursos · Biblioteca</div><h1>PDFs comentados para concursos</h1><p>Explore materiais comentados organizados por curso e assunto. Consulte a descrição e a prévia antes de acessar o arquivo completo.</p></header>
<form method="GET" action="{{ route('library.materials.index') }}" class="pm-lib-filter">
<label>Curso<select name="course"><option value="">Todos os cursos</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId == $course->id)>{{ $course->title }}</option>@endforeach</select></label>
<label>Pesquisar<input type="search" name="q" value="{{ request('q') }}" placeholder="Título ou assunto"></label>
<button type="submit" class="pm-lib-btn">Pesquisar</button></form>
<div class="pm-lib-grid">
@forelse($documents as $doc)
@php($detailUrl = route('library.materials.show', ['document'=>$doc->id,'slug'=>\Illuminate\Support\Str::slug($doc->title) ?: 'material-comentado']))
<article class="pm-lib-card">
<div class="pm-lib-card-head"><span class="pm-lib-symbol" aria-hidden="true">C</span><div><span class="pm-lib-chip">PDF comentado</span><div class="pm-lib-small mt-1">{{ $doc->year ?: 'Ano não informado' }}{{ $doc->exam_board ? ' · '.$doc->exam_board : '' }}</div></div></div>
<h2><a href="{{ $detailUrl }}">{{ $doc->title }}</a></h2><div class="pm-lib-small mb-2">{{ $doc->course?->title }}</div>
<p>{{ \Illuminate\Support\Str::limit($doc->description ?: 'Consulte as informações e a prévia deste documento.', 160) }}</p>
<a class="pm-lib-btn" href="{{ $detailUrl }}">Ver documento e download →</a></article>
@empty<div class="pm-lib-panel">Nenhum material encontrado para os filtros selecionados.</div>@endforelse
</div><div class="mt-4">{{ $documents->links() }}</div></div>
@endsection
