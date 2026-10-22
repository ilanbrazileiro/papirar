@extends('layouts.admin')
@section('title','PDFs comentados | Administração')
@section('content')
<div class="container-fluid py-3">
 <div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Biblioteca acadêmica de PDFs</h2><p class="text-muted">Um PDF por disciplina e tópicos, reutilizável em vários cursos. Armazenamento privado no R2.</p></div><a href="{{ route('library.materials.index') }}" class="btn btn-outline-primary" target="_blank">Ver biblioteca pública</a></div>
 @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <div class="card mb-4"><div class="card-header"><strong>Novo material comentado</strong></div><div class="card-body"><form method="POST" action="{{ route('admin.library.materials.store') }}" enctype="multipart/form-data">@csrf
<div class="form-row">
 <div class="form-group col-md-5"><label>Disciplina *</label><select id="material-subject" class="form-control" name="subject_id" required><option value="">Selecione a disciplina</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $document->subject_id ?? null) == $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
 <div class="form-group col-md-7"><label>Tópicos da disciplina * (selecione um ou mais)</label><select class="form-control" id="material-topics" name="topic_ids[]" multiple required size="7">@foreach($topics as $topic)<option value="{{ $topic->id }}" data-subject="{{ $topic->subject_id }}" @selected(in_array($topic->id,old('topic_ids',isset($document)?$document->topics->pluck('id')->all():[])))>{{ $topic->name }}</option>@endforeach</select><small class="text-muted">Ctrl + clique para múltiplos tópicos no desktop.</small></div>
</div>
<div class="form-group"><label>Título *</label><input required maxlength="240" name="title" value="{{ old('title',$document->title ?? '') }}" class="form-control"></div>
<div class="form-row"><div class="form-group col-md-3"><label>Ano/edição</label><input type="number" name="year" min="1900" max="2100" value="{{ old('year',$document->year ?? '') }}" class="form-control"></div><div class="form-group col-md-9"><label>Fonte/autoria (URL opcional)</label><input type="url" name="source_url" value="{{ old('source_url',$document->source_url ?? '') }}" class="form-control"></div></div>
<div class="form-group"><label>Descrição * (mín. 40 caracteres)</label><textarea required minlength="40" maxlength="5000" rows="3" class="form-control" name="description">{{ old('description',$document->description ?? '') }}</textarea></div>
<div class="form-group"><label>Sumário</label><textarea maxlength="12000" rows="3" class="form-control" name="summary">{{ old('summary',$document->summary ?? '') }}</textarea></div>

<div class="form-row"><div class="form-group col-md-6"><label>PDF principal * (máx. 50 MB)</label><input required type="file" accept="application/pdf" name="pdf" class="form-control-file"></div><div class="form-group col-md-6"><label>Imagem de prévia (até 5 MB)</label><input type="file" accept="image/jpeg,image/png,image/webp" name="preview" class="form-control-file"></div></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" name="is_published" id="publish-material" @checked(old('is_published'))><label class="form-check-label" for="publish-material">Publicar imediatamente</label></div>
<button class="btn btn-primary">Cadastrar material no R2</button></form></div></div>
<div class="card"><div class="card-header"><strong>Materiais cadastrados</strong> ({{ $documents->total() }})</div><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Documento</th><th>Classificação</th><th>Status</th><th>Ações</th></tr></thead><tbody>
@forelse($documents as $doc)<tr><td>{{ $doc->title }}</td><td>{{ $doc->subject?->name ?? 'Pendente de classificação' }}<br><small>{{ $doc->topics->pluck('name')->join(' • ') }}</small></td><td>{{ $doc->is_published ? 'Publicado' : 'Oculto' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.library.materials.edit',$doc) }}">Editar</a></td></tr>
@empty<tr><td colspan="4">Nenhum material cadastrado.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{ $documents->links() }}</div></div></div>
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
