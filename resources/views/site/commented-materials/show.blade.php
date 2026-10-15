@extends('site.site_layout')
@section('title',\Illuminate\Support\Str::limit($document->title.' | Material comentado | Papirar',70))
@section('meta_description',\Illuminate\Support\Str::limit($document->description,155))
@section('canonical',route('library.materials.show',[$document->id,\Illuminate\Support\Str::slug($document->title) ?: 'material-comentado']))
@section('og_title',$document->title)
@section('og_description',\Illuminate\Support\Str::limit($document->description,155))
@push('head')
<style>
.material-layout{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(280px,1fr);gap:1.4rem}.material-card{background:#fff;border:1px solid #dce4ef;border-radius:14px;padding:1.5rem;margin-bottom:1.3rem}.material-cta{display:block;border:0;border-radius:9px;background:#0c5a9f;color:#fff!important;text-decoration:none;text-align:center;font-weight:750;padding:1rem;cursor:pointer;width:100%;font-size:1rem}.material-dialog{border:0;border-radius:16px;max-width:480px;width:calc(100% - 2rem);padding:2rem;box-shadow:0 20px 70px #09233c55}.material-dialog::backdrop{background:#11284199}@media(max-width:760px){.material-layout{grid-template-columns:1fr}}
</style>
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'CreativeWork','name'=>$document->title,'description'=>$document->description,'url'=>route('library.materials.show',[$document->id,\Illuminate\Support\Str::slug($document->title) ?: 'material-comentado']),'isAccessibleForFree'=>false,'publisher'=>['@type'=>'Organization','name'=>'Papirar Concursos']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
@section('content')
@php
$courseUrl = $document->course && $document->course->landing_enabled ? route('site.course-landings.show',$document->course->slug) : route('site.courses.show',$document->course->slug);
@endphp
<div style="max-width:1130px;margin:2rem auto 5rem;padding:0 1rem">
<nav><a href="{{ route('site.home') }}">Início</a> / <a href="{{ route('library.materials.index') }}">Materiais comentados</a> / {{ $document->title }}</nav>
<header style="background:linear-gradient(115deg,#0b2443,#185c94);color:#fff;border-radius:18px;padding:2rem;margin:1.6rem 0"><div style="font-size:.88rem;letter-spacing:.09em">MATERIAL DE ESTUDO COMENTADO</div><h1 style="color:#fff;margin:.6rem 0">{{ $document->title }}</h1><p style="color:#e1ebf7">{{ $document->course?->title }}{{ $document->year ? ' · Edição '.$document->year : '' }}</p><p style="color:#e1ebf7">Conheça o material e os conteúdos abordados antes de acessar o arquivo integral.</p></header>
<div class="material-layout"><main>
<section class="material-card"><h2>Sobre o material</h2><p style="white-space:pre-line;line-height:1.7">{{ $document->description }}</p>@if($document->summary)<h3>Conteúdos abordados</h3><p style="white-space:pre-line">{{ $document->summary }}</p>@endif @if($document->source_url)<p><a href="{{ $document->source_url }}" rel="nofollow noopener noreferrer" target="_blank">Fonte/autoria do material ↗</a></p>@endif</section>
<section class="material-card"><h2>Prévia do conteúdo</h2>@if($document->preview_key)<a href="{{ route('library.materials.preview',$document) }}" target="_blank" rel="noopener noreferrer"><img style="display:block;max-width:100%;max-height:650px;margin:auto" src="{{ route('library.materials.preview',$document) }}" loading="lazy" alt="Prévia de {{ $document->title }}"></a>@else<p>O administrador ainda não cadastrou uma imagem de prévia para este material.</p>@endif</section>
</main><aside><section class="material-card" style="background:#f1f6fd"><div style="font-weight:750;font-size:.8rem;color:#385c84">ACESSO AO MATERIAL</div><h2>Estude {{ $document->course?->title }} no Papirar</h2><p>Conteúdo de apoio à preparação e recursos do curso em um único lugar.</p><ul><li>Questões para praticar</li><li>Simulados</li><li>Revisão de erros</li><li>Acompanhamento do estudo</li></ul>
@if($canDownload)<a class="material-cta" href="{{ route('library.materials.download',$document) }}">↓ Baixar PDF completo</a>@else<button type="button" class="material-cta" onclick="document.getElementById('material-subscribe-dialog').showModal()">↓ Desbloquear download do PDF</button>@endif
<p style="font-size:.86rem;color:#56677c">Download exclusivo para assinantes pagos ativos e acessos manuais autorizados expressamente.</p><a href="{{ $courseUrl }}">Conhecer o curso →</a></section></aside></div>
@if($related->isNotEmpty())<section class="material-card"><h2>Outros materiais deste curso</h2><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">@foreach($related as $item)<a href="{{ route('library.materials.show',[$item->id,\Illuminate\Support\Str::slug($item->title) ?: 'material-comentado']) }}" style="border:1px solid #dce4ef;border-radius:9px;padding:1rem">{{ $item->title }}</a>@endforeach</div></section>@endif
@if(!$canDownload)<dialog class="material-dialog" id="material-subscribe-dialog"><h2>Tenha acesso ao PDF completo</h2><p>Este material é exclusivo para assinantes pagos do curso <strong>{{ $document->course?->title }}</strong>. Cadastro gratuito e trial não liberam downloads.</p><a class="material-cta" href="{{ $courseUrl }}">Conhecer curso e assinatura</a><form method="dialog" style="margin-top:1rem"><button>Fechar</button></form></dialog>@endif
</div>
@endsection
