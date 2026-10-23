@extends('site.site_layout')
@section('title','Resultado: '.$sim->title.' | Papirar')
@section('robots','noindex,nofollow')
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}?v=3.1">@endpush
@section('content')
@php
$total=(int)$attempt->question_count;
$correct=(int)$attempt->correct_answers;
$wrong=max(0,$total-$correct);
$rate=$total>0?(int)round(100*$correct/$total):0;
$missRate=100-$rate;
@endphp
<div class="ps-wrap ps-result">
    <header class="ps-result-hero"><span class="ps-eyebrow ps-eyebrow-light">SIMULADO CONCLUÍDO</span><h1>Seu resultado está pronto</h1><p>{{ $sim->title }}</p></header>
    <section class="ps-card ps-score-card" aria-label="Resultado do simulado">
        <div class="ps-score-intro"><div><span class="ps-eyebrow">SEU APROVEITAMENTO</span><h2>{{ $rate }}<span>%</span></h2><p>{{ $correct }} de {{ $total }} questões corretas</p></div><div class="ps-score-message"><strong>{{ $rate>=80?'Excelente desempenho!':($rate>=60?'Bom progresso!':($rate>=40?'Continue evoluindo!':'Cada questão é uma oportunidade de aprender.') ) }}</strong><span>Use o gabarito comentado abaixo para entender as respostas e melhorar seu próximo resultado.</span></div></div>
        <div class="ps-score-stats"><div class="ps-stat ps-stat-correct"><strong>{{ $correct }}</strong><span>Acertos</span></div><div class="ps-stat ps-stat-wrong"><strong>{{ $wrong }}</strong><span>Erros e em branco</span></div><div class="ps-stat"><strong>{{ $total }}</strong><span>Total de questões</span></div></div>
        <div class="ps-bar-heading"><span>Distribuição das respostas</span><strong>{{ $rate }}% de acertos</strong></div>
        <div class="ps-score-bar" role="img" aria-label="{{ $rate }} por cento de acertos e {{ $missRate }} por cento de erros ou respostas em branco"><div class="ps-bar-good" style="width:{{ $rate }}%"></div><div class="ps-bar-bad" style="width:{{ $missRate }}%"></div></div>
        <div class="ps-bar-legend"><span><i class="ps-dot ps-dot-good"></i>Acertos {{ $rate }}%</span><span><i class="ps-dot ps-dot-bad"></i>Erros e em branco {{ $missRate }}%</span></div>
    </section>
    <div class="ps-result-convert">
        <div class="ps-result-intro"><span class="ps-eyebrow">PRÓXIMO PASSO</span><h2>Seu resultado é só o começo</h2><p>Transforme seus erros em aprendizado. Continue praticando com questões direcionadas e acompanhe sua evolução no Papirar.</p>
        @if(auth()->guest())<a class="ps-button ps-button-primary" href="{{ route('register') }}">Criar conta gratuita →</a>@endif
        <a class="ps-button ps-button-outline" href="{{ route('public-simulations.index') }}">Explorar simulados</a>
        <form method="POST" action="{{ route('public-simulations.start',$sim->slug) }}" class="mt-3">
            @csrf
            <input type="hidden" name="replay" value="1">
            <button type="submit" class="ps-button ps-button-primary">Refazer este simulado →</button>
        </form>
        <p class="ps-small">Sua nova tentativa será registrada separadamente, sem apagar os resultados anteriores.</p></div>
        @include('site.public-simulations.course-card')
    </div>
    @if(isset($history) && $history->count() > 1)
        <section class="ps-card mb-4" aria-label="Histórico de tentativas">
            <h2 class="h5">Suas tentativas anteriores</h2>
            <p class="ps-small">Selecione uma tentativa para consultar o resultado e o gabarito correspondente.</p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($history as $previous)
                    <a class="ps-button ps-button-outline" href="{{ route('public-simulations.result', ['slug'=>$sim->slug,'attempt'=>$previous->id]) }}">
                        {{ $previous->completed_at->format('d/m/Y H:i') }} · {{ $previous->correct_answers }}/{{ $previous->question_count }} acertos
                    </a>
                @endforeach
            </div>
        </section>
    @endif
    <section class="ps-answer-key" id="gabarito"><div class="ps-answer-key-intro"><span class="ps-eyebrow">APRENDA COM CADA RESPOSTA</span><h2>Gabarito comentado completo</h2><p>Confira sua escolha, a alternativa correta e a explicação de cada questão.</p></div>
        @foreach($questions as $question)
            @php
                $selected=$attempt->answers_json[(string)$question->id]??null;
                $right=$question->alternatives->first(fn($a)=>(bool)$a->is_correct);
                $isCorrect=$selected!==null && (int)$selected===(int)($right?->id);
            @endphp
            <article class="ps-card ps-answer-card">
                <div class="ps-answer-head"><span class="ps-number">QUESTÃO {{ $loop->iteration }} / {{ $questions->count() }}</span><span class="ps-answer-status {{ $isCorrect?'ps-answer-good':'ps-answer-bad' }}">{{ $isCorrect?'✓ Acertou':($selected===null?'Em branco':'✕ Errou') }}</span></div>
                @if(filled($question->source_reference))
                    <div class="ps-source-reference"><span class="ps-source-icon" aria-hidden="true">▤</span><span><strong>Referência da questão</strong><span>{{ $question->source_reference }}</span></span></div>
                @endif
                <div class="ps-question-statement">{!! $question->statement !!}</div>
                <div class="ps-options ps-review-options">
                    @foreach($question->alternatives as $option)
                    <div class="ps-option {{ $option->is_correct?'is-right':'' }} {{ $selected!==null && (int)$selected===(int)$option->id && !$option->is_correct?'is-wrong':'' }}"><span class="ps-letter">{{ $option->letter }}</span><span class="ps-option-text">{!! $option->text !!}</span>@if($option->is_correct)<strong class="ps-option-status">Correta</strong>@elseif($selected!==null && (int)$selected===(int)$option->id)<strong class="ps-option-status">Sua escolha</strong>@endif</div>
                    @endforeach
                </div>
                <div class="ps-commentary"><strong>Comentário da questão</strong><div>{!! $question->commented_answer ?: 'Comentário ainda não disponível.' !!}</div></div>
            </article>
        @endforeach
    </section>
    <div class="ps-bottom-cta"><strong>Quer continuar evoluindo?</strong><p>Conheça a preparação completa para {{ $sim->course->title }}.</p><a class="ps-button ps-button-light" href="{{ $sim->course->landing_enabled ? route('site.course-landings.show',$sim->course->slug) : route('site.courses.show',$sim->course->slug) }}">Conhecer o curso →</a></div>
</div>
@endsection
