@extends('layouts.admin')

@section('title', 'Bancas | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">Bancas organizadoras</h1>
            <p class="text-muted mb-0">Cadastre e padronize as bancas usadas nas questões.</p>
        </div>

        <a href="{{ route('admin.exam-boards.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova banca
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.exam-boards.index') }}" class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Nome, slug ou descrição">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        @if($examBoards->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Questões</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examBoards as $examBoard)
                            <tr>
                                <td>
                                    <strong>{{ $examBoard->name }}</strong>
                                    @if($examBoard->description)
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($examBoard->description, 100) }}</div>
                                    @endif
                                </td>
                                <td><code>{{ $examBoard->slug }}</code></td>
                                <td>{{ number_format($examBoard->questions_count, 0, ',', '.') }}</td>
                                <td>
                                    @if($examBoard->active)
                                        <span class="badge bg-success">Ativa</span>
                                    @else
                                        <span class="badge bg-secondary">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.questions.index', ['exam_board_id' => $examBoard->id]) }}" class="btn btn-sm btn-outline-secondary">Ver questões</a>
                                    <a href="{{ route('admin.exam-boards.edit', $examBoard) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('admin.exam-boards.destroy', $examBoard) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir esta banca?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" @disabled($examBoard->questions_count > 0)>Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $examBoards->links() }}
            </div>
        @else
            <div class="card-body text-muted">Nenhuma banca cadastrada.</div>
        @endif
    </div>
</div>
@endsection
