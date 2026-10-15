@extends('site.site_layout')
@section('title','Provas anteriores PMERJ e CBMERJ | Papirar Concursos')
@section('meta_description','Consulte provas oficiais anteriores para concursos militares, com informações de banca, ano, sumário e prévia. Downloads para assinantes do Papirar.')
@section('canonical',route('library.exams.index'))
@section('content')
<section class="site-container" style="max-width:1100px;margin:2rem auto 5rem;padding:0 1rem">
<nav aria-label="Navegação"><a href="{{ route('site.home') }}">Início</a> / Provas anteriores</nav>
<h1 style="margin-top:1.5rem">Provas anteriores de concursos</h1>
<p>Explore as provas oficiais cadastradas para os concursos atendidos pelo Papirar. Consulte informações, conteúdo programático resumido e prévias antes de contratar.</p>
<form method="GET" action="{{ route('library.exams.index') }}" style="display:flex;gap:.7rem;flex-wrap:wrap;margin:1.5rem 0"><label style="flex:1;min-width:230px">Curso<br><select name="course" style="width:100%;padding:.7rem"><option value="">Todos os cursos</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId==$course->id)>{{ $course->title }}</option>@endforeach</select></label><label style="flex:2;min-width:230px">Pesquisar prova<br><input type="search" name="q" value="{{ request('q') }}" placeholder="Ex.: soldado PMERJ" style="width:100%;padding:.7rem"></label><button type="submit" style="align-self:end;padding:.75rem 1.4rem;background:#0b3b71;color:#fff;border:0;border-radius:7px">Filtrar</button></form>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(265px,1fr));gap:1rem">
@forelse($documents as $doc)<article style="padding:1.4rem;border:1px solid #e1e5eb;border-radius:12px;background:#fff;display:flex;flex-direction:column"><div style="color:#586777;font-size:.85rem">{{ $doc->course?->title }} • {{ $doc->year ?: 'Ano não informado' }}</div><h2 style="font-size:1.25rem;line-height:1.3;margin:.7rem 0"><a href="{{ $doc->publicUrl() }}">{{ $doc->title }}</a></h2><p style="font-size:.91rem">{{ $doc->exam_board ?: 'Banca não informada' }}</p><p style="flex:1">{{ \Illuminate\Support\Str::limit($doc->description ?: 'Confira informações e prévia desta prova oficial.',170) }}</p><a href="{{ $doc->publicUrl() }}" style="color:#075cad;font-weight:700">Ver detalhes e prévia →</a></article>
@empty<p>Nenhuma prova corresponde aos filtros selecionados.</p>@endforelse
</div><div style="margin-top:2rem">{{ $documents->links() }}</div>
</section>
@endsection
