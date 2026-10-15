@extends('site.site_layout')
@section('title', \Illuminate\Support\Str::limit($document->title.' | Prova anterior | Papirar',65))
@section('meta_description', \Illuminate\Support\Str::limit($document->description ?: ('Consulte a prova '.$document->title.' com sumário, dados da banca e prévia no Papirar Concursos.'),155))
@section('canonical',$document->publicUrl())
@section('og_title',$document->title)
@section('og_description',\Illuminate\Support\Str::limit($document->description ?: 'Confira informações e prévia desta prova oficial.',170))
@push('head')
<style>
.papirar-proof-wrap{max-width:1130px;margin:2.2rem auto 5rem;padding:0 1.2rem;color:#102139}.papirar-proof-wrap a{color:#11549d}.papirar-proof-hero{background:linear-gradient(110deg,#0b203b,#164e86);color:#fff;padding:2.4rem;border-radius:18px;margin:1.5rem 0 2rem}.papirar-proof-hero h1{font-size:clamp(1.9rem,3.4vw,2.8rem);color:#fff;margin:.7rem 0}.papirar-proof-hero p{max-width:740px;color:#e1eafa;line-height:1.6}.papirar-proof-meta{display:flex;gap:.55rem;flex-wrap:wrap}.papirar-proof-meta span{border:1px solid #7595bc;background:#234b78;border-radius:30px;padding:.35rem .8rem;font-size:.9rem}.papirar-proof-columns{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(265px,1fr);gap:1.4rem;align-items:start}.papirar-proof-panel{border:1px solid #e0e7f1;border-radius:15px;padding:1.6rem;background:#fff;margin-bottom:1.3rem}.papirar-proof-panel h2{font-size:1.35rem;margin:0 0 1rem}.papirar-proof-panel p{line-height:1.7}.papirar-proof-aside{background:#f1f6fc;border:1px solid #d7e4f5;border-radius:15px;padding:1.6rem}.papirar-proof-aside ul{padding-left:1.25rem;line-height:1.95}.papirar-proof-cta{display:block;width:100%;text-align:center;border:0;border-radius:9px;background:#14599b;color:#fff!important;padding:.9rem 1rem;font-weight:700;cursor:pointer;text-decoration:none}.papirar-proof-note{font-size:.82rem;color:#56657c;margin-top:.7rem}.papirar-proof-related{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem}.papirar-proof-related a{display:block;border:1px solid #e0e7f1;border-radius:10px;padding:1rem;text-decoration:none}.papirar-proof-related a:hover{border-color:#14599b}.papirar-proof-placeholder{padding:3rem 1rem;text-align:center;border:1px dashed #bbcde3;border-radius:12px;background:#f7faff;color:#526580}.papirar-proof-dialog{width:calc(100% - 2rem);max-width:510px;border:0;border-radius:17px;padding:2rem;box-shadow:0 16px 45px #071e3c45}.papirar-proof-dialog::backdrop{background:#081b3299}.papirar-proof-dialog h2{font-size:1.5rem}.papirar-proof-dialog ul{line-height:1.9}.papirar-proof-close{background:transparent;border:1px solid #adb8c8;border-radius:7px;padding:.7rem 1rem;cursor:pointer;margin-top:.8rem}@media(max-width:760px){.papirar-proof-columns{grid-template-columns:1fr}.papirar-proof-hero{padding:1.6rem}.papirar-proof-aside{order:-1}}
</style>
@push('head')
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'CreativeWork','name'=>$document->title,'description'=>$document->description ?: $document->title,'url'=>$document->publicUrl(),'datePublished'=>$document->year ? (string)$document->year : null,'isAccessibleForFree'=>false,'publisher'=>['@type'=>'Organization','name'=>'Papirar Concursos']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
@section('content')
@php
$courseUrl = $document->course && $document->course->landing_enabled ? route('site.course-landings.show', $document->course->slug) : ($document->course ? route('site.courses.show', $document->course->slug) : route('library.exams.index'));
@endphp
<div class="papirar-proof-wrap">
<nav aria-label="Navegação"><a href="{{ route('site.home') }}">Início</a> / <a href="{{ route('library.exams.index') }}">Provas anteriores</a> / {{ $document->title }}</nav>
<header class="papirar-proof-hero">
<div style="font-size:.9rem;font-weight:600;letter-spacing:.04em">BIBLIOTECA DE PROVAS OFICIAIS</div>
<h1>{{ $document->title }}{{ $document->year ? ' — '.$document->year : '' }}{{ $document->exam_board ? ' | '.$document->exam_board : '' }}</h1>
<p>Consulte as informações desta prova anterior, conheça os assuntos disponíveis e encontre recursos para orientar seus estudos. O documento completo está disponível para assinantes pagos do curso associado.</p>
<div class="papirar-proof-meta"><span>{{ $document->course?->title ?? 'Concurso' }}</span>@if($document->year)<span>Ano {{ $document->year }}</span>@endif @if($document->exam_board)<span>Banca {{ $document->exam_board }}</span>@endif<span>PDF oficial</span></div>
</header>
<div class="papirar-proof-columns">
<main>
<section class="papirar-proof-panel"><h2>Sobre esta prova</h2><p style="white-space:pre-line">{{ $document->description ?: 'Consulte os dados cadastrados desta prova oficial. O conteúdo completo pode ser acessado por assinantes do curso correspondente.' }}</p>
@if($document->summary)<h3 style="font-size:1.13rem">Conteúdo e sumário</h3><p style="white-space:pre-line">{{ $document->summary }}</p>@endif
@if($document->source_url)<p><a href="{{ $document->source_url }}" target="_blank" rel="noopener noreferrer nofollow">Consultar fonte oficial da prova ↗</a></p>@endif</section>
<section class="papirar-proof-panel"><h2>Prévia da prova</h2>@if($document->preview_key)<a href="{{ route('library.exams.preview',$document) }}" target="_blank" rel="noopener noreferrer"><img src="{{ route('library.exams.preview',$document) }}" alt="Prévia de {{ $document->title }}" loading="lazy" style="display:block;max-width:100%;max-height:650px;margin:auto;border-radius:9px"><p style="text-align:center">Ampliar imagem de prévia</p></a>@else<div class="papirar-proof-placeholder"><strong>Prévia ainda não cadastrada</strong><p>As informações e a descrição desta prova estão disponíveis nesta página. O PDF completo exige acesso autorizado.</p></div>@endif</section>
</main>
<aside class="papirar-proof-aside"><div style="font-size:.75rem;letter-spacing:.08em;color:#435f80;font-weight:800">PREPARAÇÃO DIRECIONADA</div><h2 style="font-size:1.45rem;line-height:1.25">Estude com o Papirar para {{ $document->course?->title }}</h2>
<p>Além de consultar provas anteriores, organize sua preparação em uma única plataforma:</p>
<ul><li>Questões para praticar</li><li>Simulados e resultados</li><li>Acompanhamento de desempenho</li><li>Revisão de erros</li><li>Plano de estudos</li></ul>
@if($canDownload)
<a class="papirar-proof-cta" href="{{ route('library.exams.download',$document) }}">Baixar PDF completo</a>
@else
<button class="papirar-proof-cta" type="button" onclick="document.getElementById('papirar-prova-dialog').showModal()">Desbloquear download da prova</button>
@endif
<p class="papirar-proof-note">Download restrito a assinantes pagos ativos do curso e acessos manuais expressamente autorizados.</p>
<a href="{{ $courseUrl }}" style="display:block;text-align:center;font-weight:700;margin-top:.8rem">Conhecer o curso →</a>
</aside></div>
@if($related->isNotEmpty())<section class="papirar-proof-panel" style="margin-top:1.4rem"><h2>Outras provas deste curso</h2><div class="papirar-proof-related">@foreach($related as $item)<a href="{{ $item->publicUrl() }}"><strong>{{ $item->title }}</strong><div style="font-size:.87rem;margin-top:.5rem;color:#53677f">{{ $item->year ?? 'Ano não informado' }}{{ $item->exam_board ? ' · '.$item->exam_board : '' }}</div></a>@endforeach</div></section>@endif
<section class="papirar-proof-panel" style="background:#f4f8fd"><h2>Continue sua preparação</h2><p>Pratique questões, faça simulados e acompanhe seus resultados no curso {{ $document->course?->title }}.</p><a href="{{ $courseUrl }}" style="font-weight:800">Conhecer o curso e os planos disponíveis →</a></section>
@if(!$canDownload)<dialog id="papirar-prova-dialog" class="papirar-proof-dialog" aria-labelledby="papirar-dialog-title"><h2 id="papirar-dialog-title">Desbloqueie esta prova e avance nos estudos</h2><p>O PDF completo é exclusivo para assinantes pagos ativos do curso <strong>{{ $document->course?->title }}</strong>.</p><ul><li>Biblioteca de provas oficiais</li><li>Questões e simulados do curso</li><li>Revisão de erros e acompanhamento</li></ul><p class="papirar-proof-note">Cadastro gratuito e período trial não habilitam o download.</p><a class="papirar-proof-cta" href="{{ $courseUrl }}">Conhecer planos e assinar</a><form method="dialog"><button class="papirar-proof-close">Agora não</button></form></dialog>@endif
</div>
@endsection
