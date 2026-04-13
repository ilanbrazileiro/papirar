@extends('admin.layout')

@section('title', 'Questões')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Questões</h1>
            <p class="page-subtitle">Gerencie as questões com filtros, vínculos e status de publicação.</p>
        </div>
        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">Nova questão</a>
    </div>

    <div class="panel p-4 mb-4">
        <form method="GET" action="{{ route('admin.questions.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-2">
                    <label class="form-label">Corporação</label>
                    <select name="corporation_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected($corporationId == $corporation->id)>{{ $corporation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Disciplina</label>
                    <select name="subject_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($subjectId == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="draft" @selected($status === 'draft')>Rascunho</option>
                        <option value="published" @selected($status === 'published')>Publicada</option>
                        <option value="archived" @selected($status === 'archived')>Arquivada</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Dificuldade</label>
                    <select name="difficulty" class="form-select">
                        <option value="">Todas</option>
                        <option value="easy" @selected($difficulty === 'easy')>Fácil</option>
                        <option value="medium" @selected($difficulty === 'medium')>Média</option>
                        <option value="hard" @selected($difficulty === 'hard')>Difícil</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Enunciado ou referência">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel p-4">
        @if($questions->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Corporação</th>
                            <th>Disciplina</th>
                            <th>Concurso</th>
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
                                <td>{{ ucfirst($question->difficulty) }}</td>
                                <td>
                                    @if($question->status === 'published')
                                        <span class="badge text-bg-success">Publicada</span>
                                    @elseif($question->status === 'draft')
                                        <span class="badge text-bg-warning">Rascunho</span>
                                    @else
                                        <span class="badge text-bg-secondary">Arquivada</span>
                                    @endif
                                </td>
                                <td style="max-width: 420px;">{{ \Illuminate\Support\Str::limit(strip_tags($question->statement), 120) }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Deseja remover esta questão?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $questions->links() }}
            </div>
        @else
            <div class="text-muted">Nenhuma questão encontrada.</div>
        @endif
    </div>
@endsection
