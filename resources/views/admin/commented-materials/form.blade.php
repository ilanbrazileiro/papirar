@extends('layouts.admin')
@section('title',($document?'Editar':'Novo').' PDF comentado | Papirar')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2>{{ $document?'Editar':'Cadastrar' }} PDF acadêmico</h2><a class="btn btn-outline-secondary" href="{{ route('admin.library.materials.index') }}">Voltar à lista</a></div>
@if($errors->any())<div class="alert alert-danger"><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
<div class="card"><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ $document?route('admin.library.materials.update',$document):route('admin.library.materials.store') }}">
@csrf 
@if($document)
@method('PATCH')
@endif
<div class="row"><div class="col-md-5 form-group"><label>Disciplina *</label><select id="material-subject" class="form-control" name="subject_id" required><option value="">Selecione</option>
@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id',$document?->subject_id)==$subject->id)>{{ $subject->name }}</option>
@endforeach</select></div>
<div class="col-md-7 form-group"><label>Tópicos da disciplina * (um ou mais)</label><select class="form-control" id="material-topics" name="topic_ids[]" multiple required size="7">
@foreach($topics as $topic)<option value="{{ $topic->id }}" data-subject="{{ $topic->subject_id }}" @selected(in_array($topic->id,old('topic_ids',$document?$document->topics->pluck('id')->all():[])))>{{ $topic->name }}</option>
@endforeach</select><small class="text-muted">Ctrl + clique permite selecionar vários tópicos.</small></div></div>
<div class="form-group"><label>Título *</label><input required maxlength="240" name="title" value="{{ old('title',$document?->title) }}" class="form-control"></div>
<div class="row"><div class="col-md-3 form-group"><label>Ano/edição</label><input type="number" name="year" min="1900" max="2100" value="{{ old('year',$document?->year) }}" class="form-control"></div><div class="col-md-9 form-group"><label>Fonte/autoria (URL)</label><input type="url" name="source_url" value="{{ old('source_url',$document?->source_url) }}" class="form-control"></div></div>
<div class="form-group"><label>Descrição * (mínimo 40 caracteres)</label><textarea required minlength="40" maxlength="5000" name="description" rows="4" class="form-control">{{ old('description',$document?->description) }}</textarea></div>
<div class="form-group"><label>Sumário</label><textarea maxlength="12000" rows="5" name="summary" class="form-control">{{ old('summary',$document?->summary) }}</textarea></div>
<div class="row"><div class="col-md-6 form-group"><label>{{ $document?'PDF já armazenado no R2':'Arquivo PDF * (máximo 50 MB)' }}</label>
@if($document)<p class="text-muted">{{ $document->file_name }} — arquivo preservado</p>
@else<input required type="file" accept="application/pdf" name="pdf" class="form-control-file">
@endif</div><div class="col-md-6 form-group"><label>Imagem de prévia (até 5 MB)</label><input type="file" accept="image/jpeg,image/png,image/webp" name="preview" class="form-control-file"></div></div>
@if($document)<input type="hidden" name="is_published" value="0">
@endif
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" name="is_published" id="published" @checked(old('is_published',$document?->is_published))><label for="published" class="form-check-label">Publicado</label></div>
<button class="btn btn-primary">Salvar material</button></form></div></div></div>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',function(){const subject=document.getElementById('material-subject'),topics=document.getElementById('material-topics');function filter(){for(const o of topics.options){let valid=o.dataset.subject===subject.value;o.hidden=!valid;o.disabled=!valid;if(!valid)o.selected=false;}}subject.addEventListener('change',filter);filter();});</script>
@endpush
