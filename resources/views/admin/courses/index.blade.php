@extends('layouts.admin')

@section('title', 'Cursos | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Cursos</h1>
            <p class="text-muted mb-0">Gerencie os produtos vendidos aos alunos.</p>
        </div>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo curso
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ $search }}" placeholder="Nome, slug ou descrição curta">
                </div>
                <div class="col-md-3">
                    <label for="course_type" class="form-label">Tipo</label>
                    <select name="course_type" id="course_type" class="form-control">
                        <option value="">Todos</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($courseType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="active" @selected($status === 'active')>Ativos</option>
                        <option value="inactive" @selected($status === 'inactive')>Inativos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Limpar</a>
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
                        <th>Curso</th>
                        <th>Tipo</th>
                        <th>Preço</th>
                        <th>Concurso vinculado</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>
                                <strong>{{ $course->title }}</strong>
                                <div class="text-muted small">{{ $course->slug }}</div>
                            </td>
                            <td>{{ $course->typeLabel() }}</td>
                            <td>
                                <div>Mensal: R$ {{ number_format((float) $course->price, 2, ',', '.') }}</div>
                                @if($course->quarterly_price)
                                    <div class="text-muted small">Trimestral: R$ {{ number_format((float) $course->quarterly_price, 2, ',', '.') }}</div>
                                @endif
                                @if($course->semiannual_price)
                                    <div class="text-muted small">Semestral: R$ {{ number_format((float) $course->semiannual_price, 2, ',', '.') }}</div>
                                @endif
                            </td>
                            <td>
                                @if($course->exam)
                                    {{ $course->exam->title }}
                                    <div class="text-muted small">{{ $course->exam->corporation->name ?? $course->corporation->name ?? '-' }}</div>
                                @else
                                    <span class="text-muted">Sem concurso vinculado</span>
                                @endif
                            </td>
                            <td>
                                @if($course->active)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif

                                @if($course->is_public)
                                    <span class="badge bg-info">Público</span>
                                @else
                                    <span class="badge bg-light text-dark">Oculto</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum curso cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($courses->hasPages())
            <div class="card-footer">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
