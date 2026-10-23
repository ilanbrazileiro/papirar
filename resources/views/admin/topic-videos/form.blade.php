@extends('layouts.admin')
@section('title',($lesson?'Editar':'Nova').' videoaula | Papirar')
@section('content')
<div class="container-fluid py-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2>{{ $lesson?'Editar':'Cadastrar' }} videoaula</h2><a class="btn btn-outline-secondary" href="{{ route('admin.topic-videos.index') }}">Voltar à lista</a></div>
@if($errors->any())<div class="alert alert-danger"><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.topic-videos.save') }}">
@csrf
@if($lesson)<input type="hidden" name="id" value="{{ $lesson->id }}">
@endif
<div class="row"><div class="col-md-6 form-group"><label>Disciplina *</label><select name="subject_id" id="tv-subject" class="form-control" required><option value="">Selecione</option>
@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id',$lesson?->subject_id)==$subject->id)>{{ $subject->name }}</option>
@endforeach</select></div><div class="col-md-6 form-group"><label>Tópico *</label><select name="topic_id" id="tv-topic" class="form-control" required><option value="">Selecione</option>
@foreach($topics as $topic)<option data-subject="{{ $topic->subject_id }}" value="{{ $topic->id }}" @selected(old('topic_id',$lesson?->topic_id)==$topic->id)>{{ $topic->name }}</option>
@endforeach</select></div></div>
<div class="row"><div class="col-md-8 form-group"><label>Título *</label><input name="title" value="{{ old('title',$lesson?->title) }}" required maxlength="255" class="form-control"></div><div class="col-md-4 form-group"><label>Origem *</label><select name="origin" required class="form-control"><option value="youtube" @selected(old('origin',$lesson?->origin ?? 'youtube')==='youtube')>YouTube — gratuito para cadastrados</option><option value="papirar" @selected(old('origin',$lesson?->origin)==='papirar')>Papirar — exclusivo</option></select></div></div>
<div class="form-group"><label>URL do vídeo *</label><input name="video_url" type="url" value="{{ old('video_url',$lesson?->video_url) }}" required class="form-control"></div>
<div class="form-group"><label>Descrição</label><textarea name="description" rows="3" class="form-control">{{ old('description',$lesson?->description) }}</textarea></div>
<div class="row"><div class="col-md-4 form-group"><label>Professor</label><input name="teacher" value="{{ old('teacher',$lesson?->teacher) }}" class="form-control"></div><div class="col-md-4 form-group"><label>Canal</label><input name="channel_name" value="{{ old('channel_name',$lesson?->channel_name) }}" class="form-control"></div><div class="col-md-2 form-group"><label>Ordem</label><input name="sort_order" type="number" value="{{ old('sort_order',$lesson?->sort_order ?? 0) }}" min="0" class="form-control"></div><div class="col-md-2 form-group"><label>Status</label><select name="status" class="form-control">
@foreach(['draft'=>'Rascunho','published'=>'Publicado','unavailable'=>'Indisponível'] as $value=>$label)<option value="{{ $value }}" @selected(old('status',$lesson?->status ?? 'draft')===$value)>{{ $label }}</option>
@endforeach</select></div></div>
<label class="d-block mb-3"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$lesson?->is_featured))> Aula principal recomendada</label>
<button class="btn btn-primary">Salvar videoaula</button></form></div></div></div>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',function(){const subject=document.getElementById('tv-subject'),topic=document.getElementById('tv-topic');function filter(){for(const option of topic.options){if(!option.dataset.subject)continue;const ok=option.dataset.subject===subject.value;option.disabled=!ok;option.hidden=!ok;if(!ok)option.selected=false;}}const current=topic.value;subject.addEventListener('change',filter);filter();if(current&&topic.querySelector('option[value="'+current+'"]:not(:disabled)'))topic.value=current;});</script>
@endpush
