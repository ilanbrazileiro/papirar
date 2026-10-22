@extends('site.site_layout')
@section('title',\Illuminate\Support\Str::limit($document->title.' | PDF comentado | Papirar',70))
@section('meta_description',\Illuminate\Support\Str::limit($document->description ?: $document->title,155))
@section('canonical',route('library.materials.show',['document'=>$document->id,'slug'=>\Illuminate\Support\Str::slug($document->title)?:'material-comentado']))
@section('og_title',$document->title)
@section('og_description',\Illuminate\Support\Str::limit($document->description ?: $document->title,155))
@push('head')<link rel="stylesheet" href="{{ asset('css/library-refresh.css') }}">@endpush
@section('content')
<div class="pm-lib"><nav><a href="{{ route('site.home') }}">Início</a> / <a href="{{ route('library.materials.index') }}">PDFs comentados</a> / {{ $document->title }}</nav>
<header class="pm-lib-hero"><div class="pm-lib-eyebrow">Material acadêmico · Papirar</div><h1>{{ $document->title }}</h1><p>{{ $document->subject?->name }} · {{ $document->topics->pluck('name')->join(', ') }}</p><p>O mesmo material pode ser utilizado em diferentes cursos que incluem estes tópicos.</p></header>
<div class="pm-lib-layout"><main>
<section class="pm-lib-panel"><h2>Sobre este documento</h2><p style="white-space:pre-line">{{ $document->description }}</p>
@if($document->summary)<h3>Conteúdo e sumário</h3><p style="white-space:pre-line">{{ $document->summary }}</p>@endif
@if($document->source_url)<a href="{{ $document->source_url }}" target="_blank" rel="noopener noreferrer nofollow">Consultar fonte indicada ↗</a>@endif</section>
<section class="pm-lib-panel"><h2>Prévia do documento</h2>
@if($document->preview_key)<a href="{{ route('library.materials.preview',$document) }}" target="_blank" rel="noopener noreferrer"><img class="pm-lib-preview" src="{{ route('library.materials.preview',$document) }}" alt="Prévia de {{ $document->title }}" loading="lazy"></a>
@else<div class="pm-lib-empty"><strong>Prévia não cadastrada</strong><p>Consulte a descrição e o sumário acima.</p></div>@endif</section></main>
<aside class="pm-lib-panel pm-lib-aside"><div class="pm-lib-eyebrow" style="color:#345c85">ACESSO AO DOCUMENTO</div><h2>{{ $document->title }}</h2>
@if($canDownload)<a class="pm-lib-btn pm-lib-download" href="{{ route('library.materials.download',$document) }}">↓ Baixar PDF completo</a>
@else<button type="button" class="pm-lib-btn pm-lib-download" onclick="document.getElementById('pm-lib-dialog').showModal()">↓ Desbloquear download</button><p class="pm-lib-small mt-2">Download exclusivo de assinantes pagos e acessos manuais autorizados em um curso compatível. Trial não libera o arquivo.</p>@endif
<hr><h3>Cursos compatíveis</h3>@forelse($relatedCourses as $course)<p><a href="{{ $course->landing_enabled ? route('site.course-landings.show',$course->slug) : route('site.courses.show',$course->slug) }}">{{ $course->title }} →</a></p>@empty<p>Nenhum curso público compatível atualmente.</p>@endforelse
</aside></div>
@if($related->isNotEmpty())<section class="pm-lib-panel"><h2>Outros materiais da disciplina</h2><div class="pm-lib-related">@foreach($related as $item)<a href="{{ route('library.materials.show',['document'=>$item->id,'slug'=>\Illuminate\Support\Str::slug($item->title)?:'material-comentado']) }}">{{ $item->title }}</a>@endforeach</div></section>@endif
@if(!$canDownload)<dialog id="pm-lib-dialog" class="pm-lib-dialog"><h2>Desbloqueie este material</h2><p>Assine um dos cursos compatíveis para baixar o PDF completo.</p><form method="dialog"><button class="btn btn-outline-secondary">Fechar</button></form></dialog>@endif
</div>
@endsection
