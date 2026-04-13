@extends('admin.layout')

@section('title', 'Concursos')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Concursos</h1>
            <p class="page-subtitle">Cadastre e mantenha os concursos vinculados às corporações.</p>
        </div>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">Novo concurso</a>
    </div>

    <div class="panel p-4 mb-4">
        <form method="GET" action="{{ route('admin.exams.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label">Corporação</label>
                    <select name="corporation_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected($corporationId == $corporation->id)>{{ $corporation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Ano</label>
                    <select name="year" class="form-select">
                        <option value="">Todos</option>
                        @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}" @selected($year == $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Título, tipo ou descrição">
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel p-4">
        @if($exams->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Corporação</th>
                            <th>Título</th>
                            <th>Ano</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                            <tr>
                                <td>{{ $exam->corporation->name ?? '-' }}</td>
                                <td class="fw-semibold">{{ $exam->title }}</td>
                                <td>{{ $exam->year }}</td>
                                <td>{{ $exam->exam_type }}</td>
                                <td>
                                    @if($exam->active)
                                        <span class="badge text-bg-success">Ativo</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('Deseja remover este concurso?');">
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
                {{ $exams->links() }}
            </div>
        @else
            <div class="text-muted">Nenhum concurso encontrado.</div>
        @endif
    </div>
@endsection
