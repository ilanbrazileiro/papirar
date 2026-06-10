@extends('layouts.admin')

@section('title', 'Questões')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Questões</h1>
        <p class="text-muted mb-0">Gerencie as questões com filtros, vínculos e status de publicação.</p>
    </div>
    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">Nova questão</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.questions.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="corporation_id" class="form-label">Corporação</label>
                    <select name="corporation_id" id="corporation_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((int) $corporationId === (int) $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="subject_id" class="form-label">Disciplina</label>
                    <select name="subject_id" id="subject_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((int) $subjectId === (int) $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="source_material_id" class="form-label">Fonte / Bibliografia</label>
                    <select name="source_material_id" id="source_material_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($sourceMaterials as $material)
                            <option value="{{ $material->id }}" @selected((int) $sourceMaterialId === (int) $material->id)>
                                {{ $material->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="draft" @selected($status === 'draft')>Rascunho</option>
                        <option value="published" @selected($status === 'published')>Publicada</option>
                        <option value="archived" @selected($status === 'archived')>Arquivada</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="difficulty" class="form-label">Dificuldade</label>
                    <select name="difficulty" id="difficulty" class="form-control">
                        <option value="">Todas</option>
                        <option value="easy" @selected($difficulty === 'easy')>Fácil</option>
                        <option value="medium" @selected($difficulty === 'medium')>Média</option>
                        <option value="hard" @selected($difficulty === 'hard')>Difícil</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ $search }}" placeholder="Buscar por enunciado, referência ou fonte">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($questions->count())
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Corporação</th>
                        <th>Disciplina</th>
                        <th>Concurso</th>
                        <th>Fonte</th>
                        <th>Dificuldade</th>
                        <th>Status</th>
                        <th>Enunciado</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questions as $question)
                        <tr>
                            <td>#{{ $question->id }}</td>
                            <td>{{ $question->corporation->name ?? '-' }}</td>
                            <td>{{ $question->subject->name ?? '-' }}</td>
                            <td>{{ $question->exam->title ?? '-' }}</td>
                            <td>
                                @if($question->sourceMaterial)
                                    <span class="badge bg-info text-dark">{{ $question->sourceMaterial->title }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($question->difficulty) }}</td>
                            <td>
                                @if($question->status === 'published')
                                    <span class="badge bg-success">Publicada</span>
                                @elseif($question->status === 'draft')
                                    <span class="badge bg-warning text-dark">Rascunho</span>
                                @else
                                    <span class="badge bg-secondary">Arquivada</span>
                                @endif
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit(strip_tags($question->statement), 120) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir esta questão?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $questions->links() }}
    </div>
@else
    <div class="alert alert-info">Nenhuma questão encontrada.</div>
@endif
@endsection
