@extends('layouts.admin')

@section('title', 'Colaboradores | Papirar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Colaboradores</h1>
        <p class="text-muted mb-0">Gerencie usuários com acesso administrativo ao Papirar.</p>
    </div>
    <a href="{{ route('admin.collaborators.create') }}" class="btn btn-primary">
        Novo colaborador
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.collaborators.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Nome ou e-mail">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Perfil</label>
                <select name="role" id="role" class="form-control">
                    <option value="">Todos</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Ativos</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inativos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                <a href="{{ route('admin.collaborators.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Cadastro</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collaborators as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $roles[$item->role] ?? $item->role }}</span>
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                        <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.collaborators.edit', $item) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Nenhum colaborador encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($collaborators->hasPages())
        <div class="card-footer">
            {{ $collaborators->links() }}
        </div>
    @endif
</div>
@endsection
