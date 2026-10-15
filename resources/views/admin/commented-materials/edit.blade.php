@extends('layouts.admin')
@section('title', 'Editar PDF comentado | Administração')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2>Editar material comentado #{{ $document->id }}</h2><a class="btn btn-outline-secondary" href="{{ route('admin.library.materials.index') }}">Voltar</a></div>
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.library.materials.update',$document) }}" enctype="multipart/form-data">@csrf @method('PATCH')
<div class="form-group"><label>Curso *</label><select class="form-control" name="course_id" required>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id',$document->course_id)==$course->id)>{{ $course->title }}</option>@endforeach</select></div>
<div class="form-group"><label>Título *</label><input class="form-control" name="title" maxlength="240" value="{{ old('title',$document->title) }}" required></div>
<div class="form-group"><label>Ano/edição</label><input class="form-control" type="number" name="year" min="1900" max="2100" value="{{ old('year',$document->year) }}"></div>
<div class="form-group"><label>Fonte/autoria (URL)</label><input class="form-control" type="url" name="source_url" value="{{ old('source_url',$document->source_url) }}"></div>
<div class="form-group"><label>Descrição *</label><textarea required minlength="40" maxlength="5000" rows="4" name="description" class="form-control">{{ old('description',$document->description) }}</textarea></div>
<div class="form-group"><label>Sumário</label><textarea maxlength="12000" rows="5" name="summary" class="form-control">{{ old('summary',$document->summary) }}</textarea></div>
<div class="form-group"><label>Substituir imagem de prévia (opcional)</label><input class="form-control-file" type="file" accept="image/jpeg,image/png,image/webp" name="preview"></div>
<p class="text-muted">O PDF principal não pode ser substituído nesta tela; isso evita perder a referência do documento existente no R2.</p>
<input type="hidden" name="is_published" value="0"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" value="1" name="is_published" id="published" @checked(old('is_published',$document->is_published))><label for="published" class="form-check-label">Publicado</label></div>
<button class="btn btn-primary">Salvar alterações</button></form></div></div></div>
@endsection
