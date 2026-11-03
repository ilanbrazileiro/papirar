@extends('layouts.admin')

@section('title', 'Concursos | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="h3 mb-0">Concursos</h1>
            <p class="text-muted mb-0">Gerencie editais, escolaridade, disciplinas e tópicos cobrados.</p>
        </div>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Novo concurso</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.exams.index') }}" class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="search" name="search" id="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Concurso ou tipo">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="corporation_id" class="form-label">Corporação</label>
                    <select name="corporation_id" id="corporation_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((int) ($corporationId ?? 0) === (int) $corporation->id)>{{ $corporation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="year" class="form-label">Ano</label>
                    <select name="year" id="year" class="form-control">
                        <option value="">Todos</option>
                        @foreach($years as $examYear)
                            <option value="{{ $examYear }}" @selected((int) ($year ?? 0) === (int) $examYear)>{{ $examYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="education_level" class="form-label">Escolaridade</label>
                    <select name="education_level" id="education_level" class="form-control">
                        <option value="">Todas</option>
                        <option value="medium" @selected(($educationLevel ?? '') === 'medium')>Médio</option>
                        <option value="higher" @selected(($educationLevel ?? '') === 'higher')>Superior</option>
                        <option value="unclassified" @selected(($educationLevel ?? '') === 'unclassified')>Não classificado</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Concurso</th>
                        <th>Corporação</th>
                        <th>Ano</th>
                        <th>Tipo</th>
                        <th>Escolaridade</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                        <tr>
                            <td>{{ $exam->id }}</td>
                            <td><strong>{{ $exam->title }}</strong></td>
                            <td>{{ $exam->corporation->name ?? '-' }}</td>
                            <td>{{ $exam->year }}</td>
                            <td>{{ $exam->exam_type }}</td>
                            <td>
                                @if($exam->education_level === 'medium')
                                    <span class="badge bg-primary">Médio</span>
                                @elseif($exam->education_level === 'higher')
                                    <span class="badge bg-info text-dark">Superior</span>
                                @else
                                    <span class="badge bg-warning text-dark">Não classificado</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $exam->status === 'planned' ? 'bg-secondary' : 'bg-success' }}">{{ $exam->status === 'planned' ? 'Previsto' : 'Publicado' }}</span>
                                @unless($exam->active)
                                    <span class="badge bg-light text-dark border">Inativo</span>
                                @endunless
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhum concurso encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
            <div class="card-footer">{{ $exams->links() }}</div>
        @endif
    </div>
</div>
@endsection
