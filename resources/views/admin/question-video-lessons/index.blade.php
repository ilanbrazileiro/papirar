@extends('layouts.admin')

@section('title', 'Aulas por questão')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Aulas por questão</h1>
            <p class="text-muted mb-0">Gerencie vídeos de resolução vinculados a questões.</p>
        </div>
        <a href="{{ route('admin.question-video-lessons.create') }}" class="btn btn-primary">Nova aula</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="form-row">
                <div class="form-group col-md-6 mb-2">
                    <label for="search">Busca</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Título, URL, ID da questão ou enunciado">
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="active" @selected($status === 'active')>Ativa</option>
                        <option value="inactive" @selected($status === 'inactive')>Inativa</option>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label for="provider">Plataforma</label>
                    <select name="provider" id="provider" class="form-control">
                        <option value="">Todas</option>
                        <option value="youtube" @selected($provider === 'youtube')>YouTube</option>
                        <option value="vimeo" @selected($provider === 'vimeo')>Vimeo</option>
                        <option value="external" @selected($provider === 'external')>Link externo</option>
                        <option value="html" @selected($provider === 'html')>HTML</option>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Aula</th>
                        <th>Questão</th>
                        <th>Plataforma</th>
                        <th>Status</th>
                        <th>Visibilidade</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lessons as $lesson)
                        <tr>
                            <td>
                                <strong>{{ $lesson->title }}</strong><br>
                                <small class="text-muted">{{ $lesson->formattedDuration() ? 'Duração: ' . $lesson->formattedDuration() : 'Sem duração' }}</small>
                            </td>
                            <td>
                                <strong>#{{ $lesson->question_id }}</strong><br>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($lesson->question->statement ?? ''), 90) }}</small>
                            </td>
                            <td>{{ $lesson->providerLabel() }}</td>
                            <td>
                                <span class="badge badge-{{ $lesson->status === 'active' ? 'success' : 'secondary' }}">{{ $lesson->statusLabel() }}</span>
                            </td>
                            <td>{{ $lesson->visibilityLabel() }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.question-video-lessons.edit', $lesson) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form method="POST" action="{{ route('admin.question-video-lessons.destroy', $lesson) }}" class="d-inline" onsubmit="return confirm('Remover esta aula?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma aula cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lessons->hasPages())
            <div class="card-footer">{{ $lessons->links() }}</div>
        @endif
    </div>
@endsection
