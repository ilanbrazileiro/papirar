@extends('site.site_layout')
@section('title','Provas oficiais de concursos | Papirar')
@section('content')
<section style="max-width:1100px;margin:2rem auto;padding:0 1rem">
<h1>Biblioteca de provas oficiais</h1><p>Consulte descrições, sumários e prévias. Download exclusivo para assinantes ativos do respectivo curso.</p>
@forelse($documents as $doc)
<article style="border:1px solid #ddd;border-radius:10px;padding:1.2rem;margin:1rem 0;background:#fff">
<h2 style="font-size:1.3rem">{{ $doc->title }}</h2><p><strong>{{ $doc->course->title }}</strong> — {{ $doc->year ?? 'Ano não informado' }} {{ $doc->exam_board ? '— '.$doc->exam_board : '' }}</p>
@if($doc->description)<p>{{ $doc->description }}</p>@endif
@if($doc->summary)<details><summary>Ver sumário</summary><p style="white-space:pre-wrap">{{ $doc->summary }}</p></details>@endif
@if($doc->preview_key)<p><img loading="lazy" alt="Prévia de {{ $doc->title }}" src="{{ route('library.exams.preview',$doc) }}" style="max-width:100%;max-height:380px"></p>@endif
@if($doc->source_url)<p>Fonte: <a href="{{ $doc->source_url }}" rel="noopener noreferrer nofollow" target="_blank">Página oficial</a></p>@endif
@php
$canDownload = auth()->check() && \App\Models\CourseAccess::query()->where('user_id',auth()->id())->where('course_id',$doc->course_id)->where('access_type','paid')->where('status','active')->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->exists();
@endphp
@if($canDownload)<a class="btn btn-primary" href="{{ route('library.exams.download',$doc) }}">Baixar prova (PDF)</a>
@else
<button type="button" onclick="document.getElementById('subscribe-{{ $doc->id }}').showModal()" style="padding:.65rem 1rem;background:#0b3b71;color:white;border:0;border-radius:6px;cursor:pointer">Baixar prova (PDF)</button>
<dialog id="subscribe-{{ $doc->id }}" style="border:1px solid #ddd;border-radius:12px;max-width:440px;padding:1.5rem">
<h3>Prepare-se com o Papirar</h3><p>Para baixar esta prova completa, é necessário ter uma assinatura paga e ativa do curso {{ $doc->course->title }}. O período trial não libera downloads.</p>
<a href="{{ route('site.courses.show',$doc->course->slug) }}" style="display:inline-block;padding:.7rem;background:#0b3b71;color:white;border-radius:6px">Conhecer curso e assinar</a>
<form method="dialog" style="margin-top:1rem"><button>Fechar</button></form></dialog>
@endif
</article>
@empty<p>Nenhuma prova disponível no momento.</p>@endforelse
{{ $documents->links() }}
</section>
@endsection
