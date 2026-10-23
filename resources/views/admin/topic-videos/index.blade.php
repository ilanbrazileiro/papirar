@extends('layouts.admin')
@section('title','Videoaulas | Papirar')
@section('content')
<div class="container-fluid py-3">
<div class="d-flex justify-content-between align-items-center mb-3"><div><h2>Videoaulas</h2><p class="text-muted mb-0">Aulas reutilizáveis por disciplina e tópico.</p></div><a class="btn btn-primary" href="{{ route('admin.topic-videos.create') }}"><i class="fas fa-plus"></i> Nova videoaula</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>ID</th><th>Aula</th><th>Disciplina / tópico</th><th>Origem</th><th>Status</th><th>Ações</th></tr></thead><tbody>
@forelse($lessons as $lesson)<tr><td>#{{ $lesson->id }}</td><td><strong>{{ $lesson->title }}</strong><div class="small text-muted">{{ $lesson->teacher }} · {{ $lesson->channel_name }}</div></td><td>{{ $lesson->subject?->name }} / {{ $lesson->topic?->name }}</td><td>{{ $lesson->origin }}</td><td>{{ $lesson->status }}</td><td><a href="{{ route('admin.topic-videos.edit',$lesson) }}" class="btn btn-sm btn-outline-primary">Editar</a> <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Abrir vídeo</a> <form method="POST" class="d-inline" action="{{ route('admin.topic-videos.destroy',$lesson) }}">
@csrf 
@method('PATCH')<button class="btn btn-sm btn-outline-danger">Despublicar</button></form></td></tr>
@empty<tr><td colspan="6">Nenhuma videoaula cadastrada.</td></tr>
@endforelse</tbody></table>{{ $lessons->links() }}</div></div></div>
@endsection
