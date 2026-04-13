@extends('admin.layout')

@section('title', 'Corporações')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Corporações</h1>
            <p class="page-subtitle">Cadastre e mantenha as corporações usadas nos filtros e nas questões.</p>
        </div>
        <a href="{{ route('admin.corporations.create') }}" class="btn btn-primary">Nova corporação</a>
    </div>

    <div class="panel p-4 mb-4">
        <form method="GET" action="{{ route('admin.corporations.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-8">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nome, slug ou descrição">
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.corporations.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel p-4">
        @if($corporations->count())
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
                        @foreach($corporations as $corporation)
                            <tr>
                                <td class="fw-semibold">{{ $corporation->name }}</td>
                                <td>{{ $corporation->slug }}</td>
                                <td>
                                    @if($corporation->active)
                                        <span class="badge text-bg-success">Ativa</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inativa</span>
                                    @endif
                                </td>
                                <td>{{ optional($corporation->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.corporations.show', $corporation) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('admin.corporations.edit', $corporation) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="{{ route('admin.corporations.destroy', $corporation) }}" onsubmit="return confirm('Deseja remover esta corporação?');">
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
                {{ $corporations->links() }}
            </div>
        @else
            <div class="text-muted">Nenhuma corporação encontrada.</div>
        @endif
    </div>
@endsection
