@extends('site.site_layout')
@section('title', \Illuminate\Support\Str::limit($document->title.' | Prova oficial | Papirar',70))
@section('meta_description', \Illuminate\Support\Str::limit($document->description ?: 'Consulte informações e prévia deste documento no Papirar.',155))
@section('canonical', $document->publicUrl())
@section('og_title',$document->title)
@section('og_description',\Illuminate\Support\Str::limit($document->description ?: 'Consulte informações e prévia do material.',155))
@push('head')
<link rel="stylesheet" href="{{ asset('css/library-refresh.css') }}">
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'CreativeWork','name'=>$document->title,'description'=>$document->description ?: $document->title,'url'=>$document->publicUrl(),'isAccessibleForFree'=>false,'publisher'=>['@type'=>'Organization','name'=>'Papirar Concursos']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
@section('content')
@php
$courseUrl = $document->course && $document->course->landing_enabled ? route('site.course-landings.show', $document->course->slug) : route('site.courses.show', $document->course->slug);
@endphp
<div class="pm-lib">
<nav aria-label="Navegação"><a href="{{ route('site.home') }}">Início</a> / <a href="{{ route('library.exams.index') }}">Provas anteriores</a> / {{ $document->title }}</nav>
<header class="pm-lib-hero"><div class="pm-lib-eyebrow">Prova oficial · Papirar</div><h1>{{ $document->title }}</h1>
<p>{{ $document->course?->title }}{{ $document->year ? ' · '.$document->year : '' }}{{ $document->exam_board ? ' · '.$document->exam_board : '' }}</p>
<p>Consulte informações e prévia do material. A versão integral está disponível para assinantes pagos ativos e acessos manuais autorizados do curso.</p></header>
<div class="pm-lib-layout"><main>
<section class="pm-lib-panel"><h2>Sobre este documento</h2><p style="white-space:pre-line">{{ $document->description ?: 'Descrição ainda não cadastrada.' }}</p>
@if($document->summary)<h3>Conteúdo e sumário</h3><p style="white-space:pre-line">{{ $document->summary }}</p>@endif
@if($document->source_url)<a href="{{ $document->source_url }}" target="_blank" rel="noopener noreferrer nofollow">Consultar fonte indicada ↗</a>@endif</section>
<section class="pm-lib-panel"><h2>Prévia do documento</h2>
@if($document->preview_key)<a href="{{ route('library.exams.preview',$document) }}" target="_blank" rel="noopener noreferrer"><img class="pm-lib-preview" src="{{ route('library.exams.preview',$document) }}" alt="Prévia de {{ $document->title }}" loading="lazy"></a>
@else<div class="pm-lib-empty"><span class="pm-lib-symbol" aria-hidden="true" style="margin:0 auto 1rem">P</span><strong>Prévia não cadastrada</strong><p class="mb-0 mt-2">Consulte a descrição acima para saber mais sobre o documento.</p></div>@endif</section>
</main><aside class="pm-lib-panel pm-lib-aside"><div class="pm-lib-eyebrow" style="color:#345c85">ACESSO AO DOCUMENTO</div><h2 class="mt-2">{{ $document->title }}</h2>
<p>Documento vinculado ao curso <strong>{{ $document->course?->title }}</strong>.</p>
@if($canDownload)
<a href="{{ route('library.exams.download',$document) }}" class="pm-lib-btn pm-lib-download">↓ Baixar PDF completo</a>
<p class="pm-lib-small mt-2">Seu acesso permite baixar este documento.</p>
@else
<button type="button" class="pm-lib-btn pm-lib-download" onclick="document.getElementById('pm-lib-dialog').showModal()">↓ Desbloquear download</button>
<p class="pm-lib-small mt-2">Download restrito a assinantes pagos ativos ou acessos manuais autorizados. Trial não libera o arquivo.</p>
@endif
<hr><p>Prepare-se com questões, simulados e acompanhamento do seu desempenho.</p><a href="{{ $courseUrl }}" style="font-weight:800">Conhecer o curso e os planos →</a></aside></div>
@if($related->isNotEmpty())<section class="pm-lib-panel"><h2>Outros documentos deste curso</h2><div class="pm-lib-related">@foreach($related as $item)<a href="{{ $item->publicUrl() }}">{{ $item->title }}<div class="pm-lib-small">{{ $item->year ?: '' }}</div></a>@endforeach</div></section>@endif
@if(!$canDownload)<dialog id="pm-lib-dialog" class="pm-lib-dialog" aria-labelledby="pm-lib-dialog-title"><h2 id="pm-lib-dialog-title">Acesse este material</h2><p>O arquivo completo exige assinatura paga ativa do curso <strong>{{ $document->course?->title }}</strong> ou acesso manual autorizado.</p><a class="pm-lib-btn" href="{{ $courseUrl }}">Conhecer o curso</a><form method="dialog" class="mt-3"><button type="submit" class="btn btn-outline-secondary">Fechar</button></form></dialog>@endif
</div>
@endsection
