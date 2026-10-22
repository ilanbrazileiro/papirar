@extends('layouts.admin')
@section('title','Editar PDF comentado | Papirar')
@section('content')
<div class="container-fluid py-3"><div class="d-flex align-items-center justify-content-between mb-3"><h2>Editar material #{{ $document->id }}</h2><a href="{{ route('admin.library.materials.index') }}" class="btn btn-outline-secondary">Voltar</a></div>
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card card-body"><form method="POST" action="{{ route('admin.library.materials.update',$document) }}" enctype="multipart/form-data">@csrf @method('PATCH')
<div class="form-row">
 <div class="form-group col-md-5"><label>Disciplina *</label><select id="material-subject" class="form-control" name="subject_id" required><option value="">Selecione a disciplina</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $document->subject_id ?? null) == $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
 <div class="form-group col-md-7"><label>Tópicos da disciplina * (selecione um ou mais)</label><select class="form-control" id="material-topics" name="topic_ids[]" multiple required size="7">@foreach($topics as $topic)<option value="{{ $topic->id }}" data-subject="{{ $topic->subject_id }}" @selected(in_array($topic->id,old('topic_ids',isset($document)?$document->topics->pluck('id')->all():[])))>{{ $topic->name }}</option>@endforeach</select><small class="text-muted">Ctrl + clique para múltiplos tópicos no desktop.</small></div>
</div>
<div class="form-group"><label>Título *</label><input required maxlength="240" name="title" value="{{ old('title',$document->title ?? '') }}" class="form-control"></div>
<div class="form-row"><div class="form-group col-md-3"><label>Ano/edição</label><input type="number" name="year" min="1900" max="2100" value="{{ old('year',$document->year ?? '') }}" class="form-control"></div><div class="form-group col-md-9"><label>Fonte/autoria (URL opcional)</label><input type="url" name="source_url" value="{{ old('source_url',$document->source_url ?? '') }}" class="form-control"></div></div>
<div class="form-group"><label>Descrição * (mín. 40 caracteres)</label><textarea required minlength="40" maxlength="5000" rows="3" class="form-control" name="description">{{ old('description',$document->description ?? '') }}</textarea></div>
<div class="form-group"><label>Sumário</label><textarea maxlength="12000" rows="3" class="form-control" name="summary">{{ old('summary',$document->summary ?? '') }}</textarea></div>

<div class="form-group"><label>Substituir prévia (opcional)</label><input type="file" name="preview" accept="image/jpeg,image/png,image/webp" class="form-control-file"></div>
<p class="text-muted">O PDF principal permanece armazenado no R2 e não é substituído nesta tela.</p>
<input type="hidden" name="is_published" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="published" value="1" name="is_published" @checked(old('is_published',$document->is_published))><label for="published" class="form-check-label">Publicado</label></div>
<button class="btn btn-primary">Salvar material</button></form></div></div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
 const subject=document.getElementById('material-subject'),topics=document.getElementById('material-topics');
 if(!subject||!topics)return;
 function filter(){Array.from(topics.options).forEach(function(option){
  const valid=option.dataset.subject===subject.value;
  option.hidden=!valid;option.disabled=!valid;
  if(!valid)option.selected=false;
 });}
 subject.addEventListener('change',filter);filter();
});
</script>
@endpush
