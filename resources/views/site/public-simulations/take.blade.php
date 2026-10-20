@extends('site.site_layout')
@section('title','Resolver '.$sim->title.' | Papirar')
@section('robots','noindex,nofollow')
@push('head')<link rel="stylesheet" href="{{ asset('css/public-simulations.css') }}?v=3.1">@endpush
@section('content')
<div class="ps-wrap ps-take">
    <div class="ps-exam-top">
        <div class="ps-exam-title"><span class="ps-eyebrow">VOCÊ ESTÁ NO SIMULADO</span><h1>{{ $sim->title }}</h1><p>Concentre-se nas questões. Seu resultado e o gabarito comentado serão exibidos ao finalizar.</p></div>
        <div class="ps-timer" id="ps-timer-box" role="timer" aria-live="off"><span>◷ TEMPO RESTANTE</span><strong id="ps-timer">--:--</strong></div>
    </div>
    <div class="ps-progress-header"><span id="ps-position">Questão 1 de {{ $questions->count() }}</span><strong id="ps-answered">0 respondidas</strong></div>
    <div class="ps-progress-track"><div id="ps-progress-fill" class="ps-progress-fill" style="width:0%"></div></div>
    <form id="ps-answer-form" method="POST" action="{{ route('public-simulations.finish',$sim->slug) }}">
        @csrf
        @foreach($questions as $question)
            <section class="ps-card ps-question ps-exam-question" id="ps-question-{{ $loop->iteration }}" data-question-index="{{ $loop->index }}" @if(!$loop->first) hidden @endif>
                <div class="ps-question-head"><span class="ps-number">QUESTÃO {{ $loop->iteration }} / {{ $questions->count() }}</span><span class="ps-question-subject">{{ $question->subject?->name ?: 'Simulado' }}</span></div>
                @if(filled($question->source_reference))
                    <div class="ps-source-reference"><span class="ps-source-icon" aria-hidden="true">▤</span><span><strong>Referência da questão</strong><span>{{ $question->source_reference }}</span></span></div>
                @endif
                <div class="ps-question-statement">{!! $question->statement !!}</div>
                <div class="ps-options">
                    @foreach($question->alternatives as $option)
                    <label class="ps-option"><input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"><span class="ps-letter">{{ $option->letter }}</span><span class="ps-option-text">{!! $option->text !!}</span></label>
                    @endforeach
                </div>
            </section>
        @endforeach
        <div class="ps-exam-actions">
            <button class="ps-button ps-button-outline" type="button" id="ps-prev" disabled>← Anterior</button>
            <button class="ps-button ps-button-primary" type="button" id="ps-next">Próxima questão →</button>
            <button class="ps-button ps-button-primary" type="submit" id="ps-finish" hidden>Finalizar e ver resultado →</button>
        </div>
        <div class="ps-question-map" aria-label="Navegação entre questões">
            <span class="ps-small">Ir para a questão:</span>
            <div class="ps-map-buttons">@foreach($questions as $question)<button type="button" data-ps-jump="{{ $loop->index }}" class="ps-map-button {{ $loop->first?'is-current':'' }}" aria-label="Ir para questão {{ $loop->iteration }}">{{ $loop->iteration }}</button>@endforeach</div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
(()=>{
 const deadline=new Date(@json($deadline->toIso8601String())).getTime(),form=document.getElementById('ps-answer-form'),timer=document.getElementById('ps-timer'),timerBox=document.getElementById('ps-timer-box'),sections=Array.from(document.querySelectorAll('.ps-exam-question')),jumps=Array.from(document.querySelectorAll('[data-ps-jump]')),total=sections.length,prev=document.getElementById('ps-prev'),next=document.getElementById('ps-next'),finish=document.getElementById('ps-finish');
 let current=0,submitted=false;
 function answered(){return sections.filter(s=>s.querySelector('input[type=radio]:checked')).length;}
 function refresh(){const n=answered();document.getElementById('ps-answered').textContent=n+' de '+total+' respondidas';document.getElementById('ps-progress-fill').style.width=(total?n/total*100:0)+'%';jumps.forEach((b,i)=>{b.classList.toggle('is-answered',!!sections[i].querySelector('input:checked'));b.classList.toggle('is-current',i===current);});}
 function show(i){current=Math.max(0,Math.min(total-1,i));sections.forEach((s,n)=>s.hidden=n!==current);document.getElementById('ps-position').textContent='Questão '+(current+1)+' de '+total;prev.disabled=current===0;next.hidden=current===total-1;finish.hidden=current!==total-1;refresh();document.querySelector('.ps-exam-top').scrollIntoView({behavior:'smooth',block:'start'});}
 prev.addEventListener('click',()=>show(current-1));next.addEventListener('click',()=>show(current+1));jumps.forEach((b,i)=>b.addEventListener('click',()=>show(i)));form.addEventListener('change',refresh);form.addEventListener('submit',e=>{if(submitted)return;const missing=total-answered();if(missing>0&&!confirm('Você deixou '+missing+' questão(ões) em branco. Deseja finalizar mesmo assim?')){e.preventDefault();return;}submitted=true;});
 function tick(){const seconds=Math.max(0,Math.ceil((deadline-Date.now())/1000));timer.textContent=String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');timerBox.classList.toggle('is-urgent',seconds<=300);timerBox.classList.toggle('is-warning',seconds>300&&seconds<=600);if(seconds===0&&!submitted){submitted=true;form.submit();}}
 refresh();tick();setInterval(tick,1000);
})();
</script>
@endpush
