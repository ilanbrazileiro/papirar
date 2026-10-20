@extends('site.site_layout')
@section('title','Resultado: '.$sim->title.' | Papirar')
@section('robots','noindex,nofollow')
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}">@endpush
@section('content')
<div class="ps-wrap"><header class="ps-hero"><h1>Seu resultado — {{ $sim->title }}</h1><p><strong>{{ $attempt->correct_answers }} de {{ $attempt->question_count }}</strong> questões corretas · {{ $attempt->question_count?round(100*$attempt->correct_answers/$attempt->question_count):0 }}% de aproveitamento.</p></header>
<div class="ps-card"><h2>Continue sua preparação</h2><p>Você já conheceu a experiência de estudo por questões do Papirar. Explore outros simulados e os recursos do curso.</p>
@if(auth()->guest())<a class="ps-button" href="{{ route('register') }}">Criar cadastro gratuito</a>@else<a class="ps-button" href="{{ $sim->course->landing_enabled ? route('site.course-landings.show',$sim->course->slug) : route('site.courses.show',$sim->course->slug) }}">Conhecer o curso</a>@endif
<a href="{{ route('public-simulations.index') }}" style="margin-left:1rem">Ver outros simulados</a></div>
<h2>Gabarito comentado completo</h2>
@foreach($questions as $question)
@php
$selected=$attempt->answers_json[(string)$question->id]??null;
$right=$question->alternatives->first(fn($a)=>(bool)$a->is_correct);
@endphp
<article class="ps-card ps-question"><h3>Questão {{ $loop->iteration }}</h3><div>{!! $question->statement !!}</div>
<p class="{{ (int)$selected===(int)($right?->id) && $selected!==null?'ps-good':'ps-bad' }}">Sua resposta: {{ $question->alternatives->firstWhere('id',(int)$selected)?->letter ?? 'Em branco' }} · Gabarito: {{ $right?->letter ?? 'Não cadastrado' }}</p>
<div class="ps-options">@foreach($question->alternatives as $option)<div><strong>{{ $option->letter }})</strong> {!! $option->text !!}</div>@endforeach</div>
<h4>Comentário</h4><div>{!! $question->commented_answer ?: 'Comentário ainda não disponível.' !!}</div></article>
@endforeach</div>
@endsection
