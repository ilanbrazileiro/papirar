@extends('site.site_layout')
@section('title','Resolver '.$sim->title.' | Papirar')
@section('robots','noindex,nofollow')
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}">@endpush
@section('content')
<div class="ps-wrap"><header class="ps-hero"><h1>{{ $sim->title }}</h1><p>Responda às {{ $questions->count() }} questões. O resultado e o gabarito comentado aparecem após a finalização.</p><p>Tempo restante: <strong id="ps-timer">—</strong></p></header>
<form id="ps-answer-form" method="POST" action="{{ route('public-simulations.finish',$sim->slug) }}">@csrf
@foreach($questions as $question)
<section class="ps-card ps-question"><h2>Questão {{ $loop->iteration }} de {{ $questions->count() }}</h2>
<div>{!! $question->statement !!}</div><div class="ps-options">@foreach($question->alternatives as $option)
<label><input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"> <strong>{{ $option->letter }})</strong> {!! $option->text !!}</label>
@endforeach</div></section>
@endforeach
<button type="submit" class="ps-button">Finalizar e ver gabarito comentado</button></form></div>
@endsection
@push('scripts')<script>
(()=>{const deadline=new Date(@json($deadline->toIso8601String())).getTime(),el=document.getElementById('ps-timer'),form=document.getElementById('ps-answer-form');let submitted=false;function tick(){let left=Math.max(0,Math.ceil((deadline-Date.now())/1000));el.textContent=Math.floor(left/60)+':'+String(left%60).padStart(2,'0');if(left<=0&&!submitted){submitted=true;form.submit()}}tick();setInterval(tick,1000)})();
</script>@endpush
