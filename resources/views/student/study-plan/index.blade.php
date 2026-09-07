@extends('layouts.student')

@section('title', 'Meu cronograma de estudos')

@push('styles')
<style>
    .plan-hero{padding:clamp(24px,4vw,40px);border-radius:26px;background:linear-gradient(135deg,#0f2344,#173b72);color:#fff;box-shadow:0 20px 48px rgba(15,35,68,.18)}
    .plan-hero h1{font-size:clamp(1.8rem,4vw,2.7rem);font-weight:950;letter-spacing:-.04em}.plan-hero p{max-width:720px;color:#dbe7f7}
    .plan-today{padding:22px;border:1px solid rgba(244,197,66,.55);border-radius:20px;background:linear-gradient(135deg,#fffdf4,#fff)}
    .plan-progress{height:10px;overflow:hidden;border-radius:999px;background:#e7edf6}.plan-progress span{display:block;height:100%;border-radius:inherit;background:#f4c542}
    .weekday-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px}.choice-card{position:relative}.choice-card input{position:absolute;opacity:0}.choice-card label{display:block;padding:12px 8px;border:1px solid var(--papirar-border);border-radius:14px;text-align:center;font-weight:800;cursor:pointer}.choice-card input:checked+label{border-color:var(--papirar-blue);background:var(--papirar-blue-soft);color:var(--papirar-navy)}
    .subject-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.subject-choice label{text-align:left;padding:12px}
    @media(max-width:767.98px){.weekday-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.subject-grid{grid-template-columns:1fr}.plan-hero{padding:22px}}
</style>
@endpush

@section('content')
<section class="plan-hero mb-4">
    <div class="small text-uppercase fw-bold text-warning mb-2">Planejamento simples e direto</div>
    <h1>Meu cronograma de estudos</h1>
    <p class="mb-0">Escolha quando estudar, quais disciplinas entram no plano e sua meta. O Papirar alterna as matérias e leva você direto às questões do dia.</p>
</section>

@if($todaySchedule)
    <section class="plan-today mb-4" data-study-plan-view data-course-id="{{ $todaySchedule['plan']->course_id }}">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="flex-grow-1">
                <div class="small text-uppercase fw-bold text-warning-emphasis">Plano de hoje</div>
                <h2 class="h4 fw-bold mb-1">{{ $todaySchedule['plan']->course->title }}</h2>
                @if($todaySchedule['is_study_day'] && $todaySchedule['subject'])
                    <div class="fw-semibold text-primary mb-2">{{ $todaySchedule['subject']->name }} · {{ $todaySchedule['answered'] }}/{{ $todaySchedule['target'] }} questões</div>
                    <div class="plan-progress"><span style="width:{{ $todaySchedule['percent'] }}%"></span></div>
                @elseif($todaySchedule['next'] && $todaySchedule['next']['subject'])
                    <div class="text-muted">Hoje é dia de descanso. Próximo estudo: {{ $todaySchedule['next']['date']->translatedFormat('l, d/m') }} · {{ $todaySchedule['next']['subject']->name }}.</div>
                @endif
            </div>
            @if($todaySchedule['is_study_day'] && $todaySchedule['subject'])
                @if($todaySchedule['completed'])
                    <span class="badge text-bg-success fs-6 p-3">Meta concluída ✓</span>
                @else
                    <form method="POST" action="{{ route('student.course-study.start', $todaySchedule['plan']->course_id) }}" data-study-plan-start>
                        @csrf
                        <input type="hidden" name="subject_ids[]" value="{{ $todaySchedule['subject']->id }}">
                        <input type="hidden" name="quantity" value="{{ $todaySchedule['remaining'] }}">
                        <input type="hidden" name="mode" value="train">
                        <button class="btn btn-warning px-4">COMEÇAR AGORA →</button>
                    </form>
                @endif
            @endif
        </div>
    </section>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('student.study-plan.store') }}" class="card-soft p-4" data-study-plan-form>
            @csrf
            <div class="section-title">{{ $plan ? 'Ajustar meu plano' : 'Criar meu plano' }}</div>
            @if($courses->isEmpty())
                <div class="alert alert-warning mb-0">Você precisa ter um curso ativo para criar um cronograma. <a href="{{ route('student.subscriptions.index') }}" class="fw-bold">Ver cursos disponíveis</a></div>
            @else
                <div class="mb-4">
                    <label class="form-label fw-bold">Curso</label>
                    <select class="form-select" name="course_id" id="plan-course" required>
                        @foreach($courses as $course)<option value="{{ $course->id }}" @selected((int)old('course_id',$plan?->course_id)===$course->id)>{{ $course->title }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold d-block">Dias disponíveis</label>
                    <div class="weekday-grid">
                        @foreach([1=>'Seg',2=>'Ter',3=>'Qua',4=>'Qui',5=>'Sex',6=>'Sáb',7=>'Dom'] as $day=>$label)
                            <div class="choice-card"><input type="checkbox" id="day-{{ $day }}" name="weekdays[]" value="{{ $day }}" @checked(in_array($day,old('weekdays',$plan?->weekdays ?? [1,2,3,4,5])))><label for="day-{{ $day }}">{{ $label }}</label></div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Meta de questões por dia</label>
                    <select class="form-select" name="daily_question_target" required>
                        @foreach([5,10,15,20,30,50] as $target)<option value="{{ $target }}" @selected((int)old('daily_question_target',$plan?->daily_question_target ?? 10)===$target)>{{ $target }} questões</option>@endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between gap-2 mb-2"><label class="form-label fw-bold mb-0">Disciplinas do plano</label><button type="button" class="btn btn-sm btn-link p-0" id="select-all-subjects">Selecionar todas</button></div>
                    <div id="plan-subjects" class="subject-grid"></div>
                    <div class="form-text">As disciplinas selecionadas serão alternadas nos seus dias de estudo.</div>
                </div>
                <button class="btn btn-primary px-4">SALVAR CRONOGRAMA</button>
            @endif
        </form>
    </div>
    <div class="col-lg-4">
        <aside class="card-soft p-4">
            <div class="section-title">Como funciona</div>
            <div class="d-grid gap-3 small-muted"><div><strong class="text-dark">1.</strong> Você define os dias e a meta.</div><div><strong class="text-dark">2.</strong> O Papirar alterna as disciplinas.</div><div><strong class="text-dark">3.</strong> O botão diário abre as questões já filtradas.</div><div><strong class="text-dark">4.</strong> As respostas atualizam o progresso automaticamente.</div></div>
            @if($plan)<form method="POST" action="{{ route('student.study-plan.destroy') }}" class="mt-4">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Desativar cronograma</button></form>@endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const data=@json($subjectsByCourse);const course=document.getElementById('plan-course');const box=document.getElementById('plan-subjects');
    const selected=@json(collect(old('subject_ids',$plan?->subject_ids ?? []))->map(fn($id)=>(int)$id)->values());
    function render(){if(!course||!box)return;box.innerHTML='';(data[course.value]||[]).forEach(function(subject){const wrap=document.createElement('div');wrap.className='choice-card subject-choice';const checked=selected.length===0||selected.includes(Number(subject.id));wrap.innerHTML='<input type="checkbox" id="subject-'+subject.id+'" name="subject_ids[]" value="'+subject.id+'" '+(checked?'checked':'')+'><label for="subject-'+subject.id+'">'+subject.name+'</label>';box.appendChild(wrap);});}
    if(course){course.addEventListener('change',function(){selected.length=0;render();});render();}
    document.getElementById('select-all-subjects')?.addEventListener('click',function(){box.querySelectorAll('input').forEach(input=>input.checked=true);});
});
</script>
@endpush
