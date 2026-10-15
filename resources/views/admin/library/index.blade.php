@extends('layouts.admin')
@section('title','Biblioteca de provas oficiais | Admin')
@section('content')
<div class="container-fluid py-3">
<h2>Provas oficiais</h2>
<p>Envie PDFs ao R2 privado. A prévia é uma imagem pública opcional; o PDF completo exige assinatura paga.</p>
<form method="POST" action="{{ route('admin.library.exams.store') }}" enctype="multipart/form-data" class="card card-body mb-4">
@csrf
@if($errors->any())<div class="alert alert-danger">{{ implode(' ', $errors->all()) }}</div>@endif
<div class="form-row"><div class="form-group col-md-6"><label>Curso</label><select name="course_id" required class="form-control"><option value="">Selecione</option>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select></div>
<div class="form-group col-md-6"><label>Título</label><input required maxlength="240" name="title" class="form-control" value="{{ old('title') }}"></div></div>
<div class="form-row"><div class="form-group col-md-3"><label>Ano</label><input type="number" min="1900" max="2100" name="year" class="form-control"></div><div class="form-group col-md-4"><label>Banca</label><input name="exam_board" class="form-control"></div><div class="form-group col-md-5"><label>Fonte oficial (URL)</label><input type="url" name="source_url" class="form-control"></div></div>
<div class="form-group"><label>Descrição</label><textarea name="description" rows="3" class="form-control"></textarea></div>
<div class="form-group"><label>Sumário</label><textarea name="summary" rows="4" class="form-control"></textarea></div>
<div class="form-row"><div class="form-group col-md-6"><label>Prova PDF (até 50 MB)</label><input type="file" name="pdf" accept="application/pdf" required class="form-control-file"></div><div class="form-group col-md-6"><label>Imagem de prévia (JPG, PNG ou WebP, até 5 MB)</label><input type="file" name="preview" accept="image/*" class="form-control-file"></div></div>
<div class="form-check mb-3"><input type="checkbox" name="is_published" value="1" id="publish" class="form-check-input"><label class="form-check-label" for="publish">Publicar imediatamente</label></div>
<button class="btn btn-primary">Enviar prova para o R2</button>
</form>
<table class="table table-striped"><thead><tr><th>Prova</th><th>Curso</th><th>Status</th><th>Ação</th></tr></thead><tbody>
@foreach($documents as $doc)<tr><td>{{ $doc->title }} ({{ $doc->year ?? 's/ano' }})</td><td>{{ $doc->course->title ?? '-' }}</td><td>{{ $doc->is_published ? 'Publicada' : 'Oculta' }}</td><td><form method="POST" action="{{ route('admin.library.exams.update',$doc) }}">@csrf @method('PATCH')<input type="hidden" name="title" value="{{ $doc->title }}"><input type="hidden" name="is_published" value="{{ $doc->is_published ? 0 : 1 }}"><button class="btn btn-sm btn-outline-primary">{{ $doc->is_published ? 'Ocultar' : 'Publicar' }}</button></form></td></tr>@endforeach
</tbody></table>{{ $documents->links() }}
</div>
@endsection
