@extends('layouts.student')
@section('title',$video->title)
@section('content')
<div class="d-flex justify-content-between flex-wrap align-items-center gap-2 mb-3"><div><div class="small-muted">{{ $course->title }} · {{ $video->subject?->name }} · {{ $video->topic?->name }}</div><h1 class="page-title">{{ $video->title }}</h1></div><a href="{{ route('student.courses.materials',$course) }}#aulas" class="btn btn-outline-primary">Voltar às aulas</a></div>
<div class="card-soft p-4"><div class="ratio ratio-16x9 bg-dark rounded-4 overflow-hidden">@if($video->embedUrl())
<iframe src="{{ $video->embedUrl() }}" title="{{ $video->title }}" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
@endif</div>
@if($video->description)<p class="mt-3">{{ $video->description }}</p>@endif
<div class="small-muted mt-3">{{ $video->teacher }} @if($video->channel_name) · Canal: {{ $video->channel_name }} @endif
@if($video->origin==='youtube') · Conteúdo disponível publicamente no YouTube @else
· Conteúdo exclusivo do Papirar
@endif</div></div>
@endsection
