@extends('layouts.admin')
@section('title','Simulados públicos')
@push('styles')
<style>
.public-sim-distribution{border:1px solid #dbe4ed;border-radius:16px;background:#fff;padding:1.25rem;max-width:850px}
.public-sim-distribution-row{display:flex;align-items:center;gap:1rem;padding:.9rem .1rem;border-bottom:1px solid #e9eef3}
.public-sim-distribution-row:last-child{border-bottom:0}
.public-sim-subject-info{flex:1;min-width:0}.public-sim-subject-name{font-weight:600;color:#17314b}.public-sim-available{display:block;font-size:.79rem;color:#66788c;margin-top:.1rem}
.public-sim-stepper{display:flex;align-items:center;gap:.65rem;flex:none}.public-sim-stepper button{width:34px;height:34px;padding:0;border:1px solid #cfd9e5;border-radius:9px;background:#f8fafc;color:#173b72;font-size:1.2rem;font-weight:700;line-height:1}.public-sim-stepper button:disabled{opacity:.3;cursor:not-allowed}
.public-sim-stepper input{width:58px;text-align:center;min-height:36px;padding:.25rem;border:1px solid #d6e1ed;border-radius:8px;font-weight:700;color:#17314b;font-variant-numeric:tabular-nums}
.public-sim-total-bar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding-top:1.15rem;font-weight:700}.public-sim-total-bar strong{font-size:1.4rem;color:#173b72}
@media(max-width:575.98px){.public-sim-distribution{padding:.9rem}.public-sim-distribution-row{gap:.5rem}.public-sim-stepper{gap:.25rem}.public-sim-stepper button{width:30px;height:32px}.public-sim-stepper input{width:43px}}
</style>
@endpush
@section('content')
<div class="container-fluid py-3"><h1>Simulados gratuitos compartilháveis</h1><p>Crie simulados com uma seleção fixa de questões existentes. O link pode ser compartilhado por WhatsApp e Instagram.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card mb-4"><div class="card-body"><h2 class="h5">{{ $editing?'Editar simulado':'Novo simulado' }}</h2>
<form method="POST" action="{{ route('admin.public-simulations.save') }}">@csrf
@if($editing)<input type="hidden" name="id" value="{{ $editing->id }}">@endif
<div class="form-group"><label>Curso *</label><select class="form-control" name="course_id" id="public-sim-course" required>@foreach($courses as $course)<option value="{{ $course->id }}" @selected((int)old('course_id',$editing?->course_id)===$course->id)>{{ $course->title }}</option>@endforeach</select></div>
<div class="form-group"><label>Título *</label><input class="form-control" name="title" value="{{ old('title',$editing?->title) }}" required maxlength="240"></div>
<div class="form-group"><label>Slug (opcional)</label><input class="form-control" name="slug" value="{{ old('slug',$editing?->slug) }}" maxlength="240"></div>
<div class="form-group"><label>Descrição</label><textarea class="form-control" name="description" rows="3">{{ old('description',$editing?->description) }}</textarea></div>
<div class="form-group"><label>Duração (minutos)</label><input type="number" class="form-control" name="duration_minutes" min="5" max="300" value="{{ old('duration_minutes',$editing?->duration_minutes??30) }}" required></div>
<div class="form-group"><label>Seleção das questões</label>
<select id="public-sim-mode" name="selection_mode" class="form-control">
<option value="lottery" @selected(old('selection_mode', $editing ? 'manual' : 'lottery')==='lottery')>Sortear por disciplina e tópicos do curso</option>
<option value="manual" @selected(old('selection_mode', $editing ? 'manual' : 'lottery')==='manual')>Selecionar IDs manualmente</option>
</select></div>
<div id="public-sim-lottery" class="mb-3 public-sim-distribution">
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2"><strong>Distribuição de questões por disciplina</strong><span class="badge badge-secondary bg-secondary">Máximo: 120 questões</span></div>
<p class="text-muted small">Defina a quantidade por disciplina com os botões − e +. Apenas questões publicadas ou revisadas nos tópicos ativos do curso entram no sorteio. Zero exclui a disciplina.</p>
<div id="public-sim-subjects" aria-live="polite">Selecione um curso para consultar as disciplinas.</div>
<div class="public-sim-total-bar"><span>Total do simulado</span><strong id="public-sim-total" aria-live="polite">0 questões</strong></div>
<p id="public-sim-warning" class="small text-danger mb-0 mt-2" role="alert" hidden></p>
</div>
<div id="public-sim-manual" class="form-group"><label>IDs das questões publicadas/revisadas, na ordem desejada (separados por vírgula)</label><textarea class="form-control" name="question_ids" rows="4">{{ old('question_ids',$editing?$editing->questions->pluck('id')->implode(', '):'') }}</textarea><small class="form-text text-muted">Use de 1 a 120 IDs. Após receber tentativas, a seleção e ordem ficam bloqueadas para preservar os resultados.</small></div>
<label class="d-block mb-3"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$editing?->is_published))> Publicado</label>
<button class="btn btn-primary">Salvar simulado</button></form></div></div>
<div class="card"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Simulado</th><th>Curso</th><th>Questões</th><th>Inícios</th><th>Conclusões</th><th>Publicação</th><th>Ações</th></tr></thead><tbody>
@forelse($simulations as $sim)<tr><td>{{ $sim->title }}</td><td>{{ $sim->course?->title }}</td><td>{{ $sim->questions_count }}</td><td>{{ $sim->attempts_count }}</td><td>{{ $sim->completed_attempts_count }}</td><td>{{ $sim->is_published?'Publicado':'Rascunho' }}</td><td><a href="{{ route('admin.public-simulations.index',['edit'=>$sim->id]) }}">Editar</a> @if($sim->is_published) · <a target="_blank" href="{{ route('public-simulations.landing',$sim->slug) }}">Link público</a>@endif</td></tr>
@empty<tr><td colspan="7">Nenhum simulado cadastrado.</td></tr>@endforelse
</tbody></table>{{ $simulations->links() }}</div></div></div>
@endsection

@push('scripts')
<script>
(function(){
 const course=document.getElementById('public-sim-course'), mode=document.getElementById('public-sim-mode');
 const lottery=document.getElementById('public-sim-lottery'), manual=document.getElementById('public-sim-manual');
 const box=document.getElementById('public-sim-subjects'), total=document.getElementById('public-sim-total');
 const warning=document.getElementById('public-sim-warning');
 const oldQuantities=@json(old('quantities', []));
 let load=0;
 function toggle(){lottery.hidden=mode.value!=='lottery';manual.hidden=mode.value!=='manual';}
 function fields(){return [...box.querySelectorAll('input[data-subject-quantity]')];}
 function currentTotal(){return fields().reduce((sum,input)=>sum+(Number(input.value)||0),0);}
 function updateTotal(){
  const sum=currentTotal();total.textContent=sum+' '+(sum===1?'questão':'questões');
  const hasError=sum>120;
  warning.hidden=!hasError;warning.textContent=hasError?'O limite de 120 questões foi ultrapassado.':'';
  for(const input of fields()){
   const max=Math.min(120,Number(input.dataset.available)||0);
   input.closest('.public-sim-stepper').querySelector('[data-step="minus"]').disabled=Number(input.value)<=0;
   input.closest('.public-sim-stepper').querySelector('[data-step="plus"]').disabled=Number(input.value)>=max||sum>=120;
  }
 }
 function setQuantity(input,n){
  const max=Math.min(120,Number(input.dataset.available)||0);
  const others=currentTotal()-(Number(input.value)||0);
  input.value=String(Math.max(0,Math.min(max,120-others,Math.floor(Number(n)||0))));
  updateTotal();
 }
 async function refresh(){
  const n=++load;box.textContent='Carregando disciplinas...';total.textContent='0 questões';warning.hidden=true;
  try{
   const response=await fetch(@json(route('admin.public-simulations.scope'))+'?course_id='+encodeURIComponent(course.value),{credentials:'same-origin',headers:{'Accept':'application/json'}});
   if(!response.ok)throw new Error('Não foi possível consultar o curso.');
   const data=await response.json();if(n!==load)return;
   box.replaceChildren();
   if(!data.subjects.length){box.textContent='Este curso não possui disciplinas ativas vinculadas.';return;}
   for(const subject of data.subjects){
    const row=document.createElement('div');row.className='public-sim-distribution-row';
    const info=document.createElement('div');info.className='public-sim-subject-info';
    const title=document.createElement('div');title.className='public-sim-subject-name';title.textContent=subject.name;
    const available=document.createElement('small');available.className='public-sim-available';available.textContent=subject.available+' questões disponíveis';
    info.append(title,available);
    const stepper=document.createElement('div');stepper.className='public-sim-stepper';
    const input=document.createElement('input');input.type='number';input.name='quantities['+subject.id+']';input.dataset.subjectQuantity='1';input.dataset.available=String(subject.available);
    input.min='0';input.max=String(Math.min(120,subject.available));input.step='1';input.value='0';input.setAttribute('aria-label','Quantidade de questões de '+subject.name);
    const minus=document.createElement('button');minus.type='button';minus.textContent='−';minus.dataset.step='minus';minus.setAttribute('aria-label','Diminuir questões de '+subject.name);
    const plus=document.createElement('button');plus.type='button';plus.textContent='+';plus.dataset.step='plus';plus.setAttribute('aria-label','Aumentar questões de '+subject.name);
    minus.addEventListener('click',()=>setQuantity(input,Number(input.value)-1));
    plus.addEventListener('click',()=>setQuantity(input,Number(input.value)+1));
    input.addEventListener('change',()=>setQuantity(input,input.value));
    input.addEventListener('input',updateTotal);
    stepper.append(minus,input,plus);row.append(info,stepper);box.append(row);
    if(Object.prototype.hasOwnProperty.call(oldQuantities,String(subject.id)))setQuantity(input,oldQuantities[String(subject.id)]);
   }
   updateTotal();
  }catch(e){if(n===load)box.textContent=e.message;}
 }
 course.addEventListener('change',refresh);mode.addEventListener('change',toggle);toggle();refresh();
})();
</script>
@endpush
