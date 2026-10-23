@extends('layouts.admin')
@section('title',($document?'Editar':'Nova').' prova oficial | Papirar')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2>{{ $document?'Editar':'Cadastrar' }} prova oficial</h2><a href="{{ route('admin.library.exams.index') }}" class="btn btn-outline-secondary">Voltar à lista</a></div>
@if($errors->any())<div class="alert alert-danger"><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
<div class="card"><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ $document?route('admin.library.exams.update',$document):route('admin.library.exams.store') }}">
@csrf 
@if($document)
@method('PATCH')
@endif
<div class="row"><div class="col-md-5 form-group"><label>Curso/concurso *</label><select name="course_id" class="form-control" required><option value="">Selecione</option>
@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id',$document?->course_id)==$course->id)>{{ $course->title }}</option>
@endforeach</select></div><div class="col-md-7 form-group"><label>Título *</label><input name="title" maxlength="240" value="{{ old('title',$document?->title) }}" required class="form-control"></div></div>
<div class="row"><div class="col-md-2 form-group"><label>Ano</label><input type="number" name="year" min="1900" max="2100" value="{{ old('year',$document?->year) }}" class="form-control"></div><div class="col-md-4 form-group"><label>Banca</label><input name="exam_board" value="{{ old('exam_board',$document?->exam_board) }}" class="form-control"></div><div class="col-md-6 form-group"><label>Fonte oficial (URL)</label><input type="url" name="source_url" value="{{ old('source_url',$document?->source_url) }}" class="form-control"></div></div>
<div class="form-group"><label>Descrição</label><textarea name="description" class="form-control" rows="3">{{ old('description',$document?->description) }}</textarea></div><div class="form-group"><label>Sumário</label><textarea name="summary" class="form-control" rows="4">{{ old('summary',$document?->summary) }}</textarea></div>
<div class="row"><div class="col-md-6 form-group"><label>{{ $document?'Arquivo atual (preservado)':'Arquivo PDF ou ZIP * (até 50 MB)' }}</label>
@if($document)<p class="text-muted">{{ $document->file_name }}</p>
@else<input type="file" name="pdf" accept=".pdf,.zip,application/pdf,application/zip" required class="form-control-file">
@endif</div><div class="col-md-6 form-group"><label>{{ $document?'Substituir prévia (opcional)':'Prévia em imagem (até 5 MB)' }}</label><input type="file" name="preview" accept="image/jpeg,image/png,image/webp" class="form-control-file"></div></div>
@if($document)<input type="hidden" name="is_published" value="0">
@endif
<div class="form-check mb-3"><input type="checkbox" id="published" name="is_published" value="1" class="form-check-input" @checked(old('is_published',$document?->is_published))><label for="published" class="form-check-label">Publicado</label></div>
<button class="btn btn-primary">Salvar prova</button></form></div></div></div>
@endsection
