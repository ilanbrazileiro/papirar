@extends('site.site_layout')
@section('title','Materiais comentados para concursos | Papirar')
@section('meta_description','Explore materiais de estudo comentados por curso, com informações e prévias públicas. PDF completo exclusivo para assinantes.')
@section('canonical',route('library.materials.index'))
@section('content')
<section style="max-width:1100px;margin:2rem auto 5rem;padding:0 1rem">
<nav><a href="{{ route('site.home') }}">Início</a> / Materiais comentados</nav>
<h1 style="margin:1.5rem 0 .6rem">Biblioteca de materiais comentados</h1>
<p>Conheça os conteúdos de apoio aos estudos, explore sumários e consulte prévias. O download integral requer assinatura paga ativa do curso correspondente.</p>
<form method="GET" action="{{ route('library.materials.index') }}" style="display:flex;gap:1rem;flex-wrap:wrap;margin:2rem 0;align-items:end"><label style="flex:1;min-width:190px">Curso<select name="course" style="display:block;width:100%;padding:.7rem"><option value="">Todos os cursos</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected($courseId == $course->id)>{{ $course->title }}</option>@endforeach</select></label><label style="flex:2;min-width:190px">Pesquisar<input name="q" type="search" value="{{ request('q') }}" placeholder="Disciplina ou assunto" style="display:block;width:100%;padding:.7rem"></label><button style="padding:.8rem 1.3rem;background:#104b83;border:0;border-radius:8px;color:white">Pesquisar</button></form>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem">
@forelse($documents as $doc)
<article style="display:flex;flex-direction:column;padding:1.4rem;border:1px solid #dbe4ee;border-radius:14px;background:#fff">
<div style="font-size:.85rem;color:#53657d">Material comentado · {{ $doc->course?->title }}</div>
<div style="font-size:2rem;margin:.75rem 0" aria-hidden="true">▤</div>
<h2 style="font-size:1.3rem;margin:0 0 .65rem"><a href="{{ route('library.materials.show',[$doc->id,\Illuminate\Support\Str::slug($doc->title) ?: 'material-comentado']) }}">{{ $doc->title }}</a></h2>
<p style="flex:1">{{ \Illuminate\Support\Str::limit($doc->description,180) }}</p>
<a style="font-weight:700;color:#10508d" href="{{ route('library.materials.show',[$doc->id,\Illuminate\Support\Str::slug($doc->title) ?: 'material-comentado']) }}">Ver conteúdo e prévia →</a>
</article>
@empty<p>Nenhum material encontrado para estes filtros.</p>@endforelse
</div><div style="margin-top:2rem">{{ $documents->links() }}</div></section>
@endsection
