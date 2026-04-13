@extends('admin.layout')

@section('title', 'Assuntos')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Assuntos</h1>
            <p class="page-subtitle">Cadastre os assuntos vinculados às disciplinas.</p>
        </div>
        <a href="{{ route('admin.topics.create') }}" class="btn btn-primary">Novo assunto</a>
    </div>

    <div class="panel p-4 mb-4">
        <form method="GET" action="{{ route('admin.topics.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Disciplina</label>
                    <select name="subject_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($subjectId == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nome, slug ou descrição">
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.topics.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel p-4">
        @if($topics->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Disciplina</th>
                            <th>Assunto</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topics as $topic)
                            <tr>
                                <td>{{ $topic->subject->name ?? '-' }}</td>
                                <td class="fw-semibold">{{ $topic->name }}</td>
                                <td>{{ $topic->slug }}</td>
                                <td>
                                    @if($topic->active)
                                        <span class="badge text-bg-success">Ativo</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.topics.show', $topic) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('admin.topics.edit', $topic) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ route('admin.topics.destroy', $topic) }}" onsubmit="return confirm('Deseja remover este assunto?');">
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
                {{ $topics->links() }}
            </div>
        @else
            <div class="text-muted">Nenhum assunto encontrado.</div>
        @endif
    </div>
@endsection
