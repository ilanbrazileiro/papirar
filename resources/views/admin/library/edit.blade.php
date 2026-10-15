@extends('layouts.admin')
@section('title','Editar prova oficial | Papirar')
@section('content')
<div class="container-fluid py-3"><a href="{{ route('admin.library.exams.index') }}">← Voltar para a biblioteca</a><h2 class="mt-3">Editar prova oficial</h2>
@if($errors->any())<div class="alert alert-danger">{{ implode(' ', $errors->all()) }}</div>@endif
<form action="{{ route('admin.library.exams.update',$document) }}" method="POST" enctype="multipart/form-data" class="card card-body">@csrf @method('PATCH')
<div class="form-group"><label>Curso *</label><select name="course_id" required class="form-control">@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id',$document->course_id)==$course->id)>{{ $course->title }}</option>@endforeach</select><small class="text-muted">Corrija aqui associações incorretas, como prova PMERJ vinculada a curso CBMERJ.</small></div>
<div class="form-group"><label>Título *</label><input name="title" required maxlength="240" value="{{ old('title',$document->title) }}" class="form-control"></div>
<div class="form-row"><div class="form-group col-md-3"><label>Ano</label><input type="number" name="year" min="1900" max="2100" value="{{ old('year',$document->year) }}" class="form-control"></div><div class="form-group col-md-4"><label>Banca</label><input name="exam_board" value="{{ old('exam_board',$document->exam_board) }}" class="form-control"></div><div class="form-group col-md-5"><label>URL da fonte oficial</label><input type="url" name="source_url" value="{{ old('source_url',$document->source_url) }}" class="form-control"></div></div>
<div class="form-group"><label>Descrição</label><textarea name="description" rows="4" class="form-control">{{ old('description',$document->description) }}</textarea></div><div class="form-group"><label>Sumário</label><textarea name="summary" rows="6" class="form-control">{{ old('summary',$document->summary) }}</textarea></div>
<div class="form-group"><label>Substituir imagem da prévia (opcional)</label><input name="preview" type="file" accept="image/jpeg,image/png,image/webp" class="form-control-file"></div>
<div class="form-group"><label>Visibilidade</label><select name="is_published" class="form-control"><option value="1" @selected(old('is_published',(int)$document->is_published)==1)>Publicada</option><option value="0" @selected(old('is_published',(int)$document->is_published)==0)>Oculta</option></select></div>
<div class="d-flex align-items-center"><button class="btn btn-primary">Salvar alterações</button>@if($document->is_published)<a class="btn btn-link" target="_blank" href="{{ $document->publicUrl() }}">Visualizar página pública</a>@endif</div>
</form></div>
@endsection
