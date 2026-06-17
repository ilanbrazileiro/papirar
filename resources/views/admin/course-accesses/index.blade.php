@extends('layouts.admin')

@section('title', 'Acessos a cursos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Acessos a cursos</h1>
            <p class="text-muted mb-0">Controle manual de acesso dos alunos aos cursos/produtos.</p>
        </div>
        <a href="{{ route('admin.course-accesses.create') }}" class="btn btn-primary">Novo acesso</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Aluno, e-mail, CPF ou curso" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="course_id" class="form-control">
                        <option value="">Todos os cursos</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) request('course_id') === (int) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">Todos os status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary btn-block">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Status</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Bônus</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accesses as $access)
                        <tr>
                            <td>
                                <strong>{{ $access->user->name ?? 'Aluno removido' }}</strong><br>
                                <small class="text-muted">{{ $access->user->email ?? '' }}</small>
                            </td>
                            <td>{{ $access->course->title ?? 'Curso removido' }}</td>
                            <td><span class="badge badge-{{ $access->status === 'active' ? 'success' : 'secondary' }}">{{ $statuses[$access->status] ?? $access->status }}</span></td>
                            <td>{{ optional($access->starts_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($access->ends_at)->format('d/m/Y H:i') ?? 'Sem limite' }}</td>
                            <td>{{ $access->bonus_days ?? 0 }} dia(s)</td>
                            <td class="text-right">
                                <a href="{{ route('admin.course-accesses.edit', $access) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                @if($access->status !== 'canceled')
                                    <form method="POST" action="{{ route('admin.course-accesses.cancel', $access) }}" class="d-inline" onsubmit="return confirm('Cancelar este acesso? O fim do período vigente será preservado.');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhum acesso encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $accesses->links() }}
    </div>
</div>
@endsection
