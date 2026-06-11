@extends('layouts.admin')

@section('title', 'Fontes e bibliografias')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Fontes e bibliografias</h1>
        <a href="{{ route('admin.source-materials.create') }}" class="btn btn-primary">Nova fonte</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.source-materials.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por título, slug, referência..." value="{{ $search }}">
                </div>
                <div class="col-md-3">
                    <select name="corporation_id" class="form-control">
                        <option value="">Todas as corporações</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((string) $corporationId === (string) $corporation->id)>{{ $corporation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="subject_id" class="form-control">
                        <option value="">Todas as disciplinas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) $subjectId === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="active" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" @selected((string) $active === '1')>Ativos</option>
                        <option value="0" @selected((string) $active === '0')>Inativos</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    <a href="{{ route('admin.source-materials.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Corporação</th>
                        <th>Disciplina</th>
                        <th>Tipo</th>
                        <th>Ano</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td>{{ $material->id }}</td>
                            <td>
                                <strong>{{ $material->title }}</strong><br>
                                <small class="text-muted">{{ $material->slug }}</small>
                            </td>
                            <td>{{ $material->corporation->name ?? 'Geral' }}</td>
                            <td>{{ $material->subject->name ?? '-' }}</td>
                            <td>{{ $material->material_type }}</td>
                            <td>{{ $material->year ?? '-' }}</td>
                            <td>
                                @if($material->active)
                                    <span class="badge bg-success">Ativo</span>
                                @else
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.source-materials.show', $material) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <a href="{{ route('admin.source-materials.edit', $material) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('admin.source-materials.destroy', $material) }}" method="POST" class="d-inline" onsubmit="return confirm('Remover esta fonte/material?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhuma fonte/material encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($materials->hasPages())
            <div class="card-footer">
                {{ $materials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
