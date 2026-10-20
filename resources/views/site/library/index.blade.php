@extends('site.site_layout')
@section('title','Provas anteriores de concursos | Papirar')
@section('meta_description','Consulte provas anteriores cadastradas com ano, banca e informações do concurso. Os PDFs completos são reservados aos assinantes autorizados.')
@section('canonical',route('library.exams.index'))
@push('head')<link rel="stylesheet" href="{{ asset('css/library-refresh.css') }}">@endpush
@section('content')
<div class="pm-lib">
<nav aria-label="Navegação"><a href="{{ route('site.home') }}">Início</a> / Provas anteriores de concursos</nav>
<header class="pm-lib-hero"><div class="pm-lib-eyebrow">Papirar Concursos · Biblioteca</div><h1>Provas anteriores de concursos</h1><p>Consulte provas anteriores cadastradas com ano, banca e informações do concurso. Os PDFs completos são reservados aos assinantes autorizados.</p></header>
<form method="GET" action="{{ route('library.exams.index') }}" class="pm-lib-filter">
<label>Curso<select name="course"><option value="">Todos os cursos</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId == $course->id)>{{ $course->title }}</option>@endforeach</select></label>
<label>Pesquisar<input type="search" name="q" value="{{ request('q') }}" placeholder="Título ou assunto"></label>
<button type="submit" class="pm-lib-btn">Pesquisar</button></form>
<div class="pm-lib-grid">
@forelse($documents as $doc)
@php($detailUrl = $doc->publicUrl())
<article class="pm-lib-card">
<div class="pm-lib-card-head"><span class="pm-lib-symbol" aria-hidden="true">P</span><div><span class="pm-lib-chip">Prova oficial</span><div class="pm-lib-small mt-1">{{ $doc->year ?: 'Ano não informado' }}{{ $doc->exam_board ? ' · '.$doc->exam_board : '' }}</div></div></div>
<h2><a href="{{ $detailUrl }}">{{ $doc->title }}</a></h2><div class="pm-lib-small mb-2">{{ $doc->course?->title }}</div>
<p>{{ \Illuminate\Support\Str::limit($doc->description ?: 'Consulte as informações e a prévia deste documento.', 160) }}</p>
<a class="pm-lib-btn" href="{{ $detailUrl }}">Ver documento e download →</a></article>
@empty<div class="pm-lib-panel">Nenhum material encontrado para os filtros selecionados.</div>@endforelse
</div><div class="mt-4">{{ $documents->links() }}</div></div>
@endsection
