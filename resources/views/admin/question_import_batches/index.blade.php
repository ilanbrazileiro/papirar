@extends('layouts.admin')

@section('title', 'Importações de questões')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Importações de questões</h1>
            <p class="text-muted mb-0">Histórico dos lotes enviados por CSV.</p>
        </div>
        <a href="{{ route('admin.questions.import.create') }}" class="btn btn-primary">
            Nova importação
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Arquivo, usuário ou e-mail">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['uploaded' => 'Enviado', 'validating' => 'Validando', 'ready' => 'Pronto para importar', 'importing' => 'Importando', 'imported' => 'Importado', 'partial' => 'Parcial', 'failed' => 'Falhou', 'cancelled' => 'Cancelado'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-secondary" type="submit">Filtrar</button>
                    <a href="{{ route('admin.question-import-batches.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Arquivo</th>
                        <th>Usuário</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Importadas</th>
                        <th>Duplicadas</th>
                        <th>Erros</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td>#{{ $batch->id }}</td>
                            <td>{{ $batch->original_filename ?? $batch->filename ?? '-' }}</td>
                            <td>{{ optional($batch->user)->name ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $batch->status }}</span></td>
                            <td>{{ $batch->total_rows }}</td>
                            <td>{{ $batch->imported_rows }}</td>
                            <td>{{ $batch->duplicate_rows }}</td>
                            <td>{{ $batch->error_rows }}</td>
                            <td>{{ optional($batch->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                @if(in_array($batch->status, ['ready', 'partial']))
                                    <a href="{{ route('admin.question-import-batches.review', $batch) }}" class="btn btn-sm btn-success">Revisar</a>
                                @endif
                                <a href="{{ route('admin.question-import-batches.show', $batch) }}" class="btn btn-sm btn-outline-primary">Detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Nenhum lote de importação registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div class="card-footer">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
