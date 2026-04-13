@extends('admin.layout')

@section('title', 'Disciplinas')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Disciplinas</h1>
            <p class="page-subtitle">Cadastre e mantenha as disciplinas usadas nas questões e nos filtros.</p>
        </div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">Nova disciplina</a>
    </div>

    <div class="panel p-4 mb-4">
        <form method="GET" action="{{ route('admin.subjects.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-8">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nome, slug ou descrição">
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel p-4">
        @if($subjects->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Criada em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr>
                                <td class="fw-semibold">{{ $subject->name }}</td>
                                <td>{{ $subject->slug }}</td>
                                <td>
                                    @if($subject->active)
                                        <span class="badge text-bg-success">Ativa</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inativa</span>
                                    @endif
                                </td>
                                <td>{{ optional($subject->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Deseja remover esta disciplina?');">
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
                {{ $subjects->links() }}
            </div>
        @else
            <div class="text-muted">Nenhuma disciplina encontrada.</div>
        @endif
    </div>
@endsection
