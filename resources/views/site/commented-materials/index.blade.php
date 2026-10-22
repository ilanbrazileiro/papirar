@extends('site.site_layout')
@section('title','PDFs comentados para concursos | Papirar')
@section('meta_description','Materiais acadêmicos organizados por disciplina e tópicos, reutilizáveis nos cursos do Papirar.')
@section('canonical',route('library.materials.index'))
@push('head')<link rel="stylesheet" href="{{ asset('css/library-refresh.css') }}">@endpush
@section('content')
<div class="pm-lib"><nav><a href="{{ route('site.home') }}">Início</a> / PDFs comentados</nav>
<header class="pm-lib-hero"><div class="pm-lib-eyebrow">Papirar Concursos · Biblioteca acadêmica</div><h1>PDFs comentados</h1><p>Materiais organizados por disciplina e tópicos, disponíveis em diferentes cursos compatíveis.</p></header>
<form method="GET" action="{{ route('library.materials.index') }}" class="pm-lib-filter">
<label>Curso<select name="course"><option value="">Todos os cursos</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId==$course->id)>{{ $course->title }}</option>@endforeach</select></label>
<label>Pesquisar<input type="search" name="q" value="{{ request('q') }}" placeholder="Título ou assunto"></label><button class="pm-lib-btn">Pesquisar</button></form>
<div class="pm-lib-grid">@forelse($documents as $doc)
@php($detailUrl=route('library.materials.show',['document'=>$doc->id,'slug'=>\Illuminate\Support\Str::slug($doc->title)?:'material-comentado']))
<article class="pm-lib-card"><div class="pm-lib-card-head"><span class="pm-lib-symbol">C</span><div><span class="pm-lib-chip">PDF comentado</span><div class="pm-lib-small">{{ $doc->year ?: 'Edição não informada' }}</div></div></div>
<h2><a href="{{ $detailUrl }}">{{ $doc->title }}</a></h2><div class="pm-lib-small mb-2">{{ $doc->subject?->name }} · {{ $doc->topics->pluck('name')->join(', ') }}</div>
<p>{{ \Illuminate\Support\Str::limit($doc->description ?: 'Veja informações e prévia deste material.',160) }}</p><a class="pm-lib-btn" href="{{ $detailUrl }}">Ver material e download →</a></article>
@empty<div class="pm-lib-panel">Nenhum material encontrado.</div>@endforelse</div><div class="mt-4">{{ $documents->links() }}</div></div>
@endsection
