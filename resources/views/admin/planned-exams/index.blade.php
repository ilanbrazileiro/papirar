@extends('admin.layout')

@section('title', 'Concursos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Concursos previstos e publicados</h1>
            <p class="text-muted mb-0">Defina o concurso, status e disciplinas cobradas para orientar o estudo do aluno.</p>
        </div>
        <a href="{{ route('admin.planned-exams.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo concurso
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Concurso</th>
                        <th>Corporação</th>
                        <th>Ano</th>
                        <th>Status</th>
                        <th>Disciplinas</th>
                        <th>Ativo</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                        <tr>
                            <td>{{ $exam->id }}</td>
                            <td class="font-weight-bold">{{ $exam->title }}</td>
                            <td>{{ $exam->corporation->name ?? '-' }}</td>
                            <td>{{ $exam->year }}</td>
                            <td>
                                @if($exam->status === 'planned')
                                    <span class="badge badge-info">Previsto</span>
                                @else
                                    <span class="badge badge-success">Publicado</span>
                                @endif
                            </td>
                            <td>{{ $exam->plannedSubjects->count() }}</td>
                            <td>
                                @if($exam->active)
                                    <span class="badge badge-success">Sim</span>
                                @else
                                    <span class="badge badge-secondary">Não</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.planned-exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhum concurso cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
            <div class="card-footer">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
