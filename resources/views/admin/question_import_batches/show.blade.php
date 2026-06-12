@extends('layouts.admin')

@section('title', 'Detalhes da importação')

@section('content')
@if(in_array($batch->status, ['ready', 'partial']))
    <div class="mb-3">
        <a href="{{ route('admin.question-import-batches.review', $batch) }}" class="btn btn-success">Revisar / importar linhas válidas</a>
    </div>
@endif
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Importação #{{ $batch->id }}</h1>
            <p class="text-muted mb-0">{{ $batch->original_filename ?? $batch->filename ?? 'Arquivo não informado' }}</p>
        </div>
        <a href="{{ route('admin.question-import-batches.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted">Total</small><h3>{{ $batch->total_rows }}</h3></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted">Válidas</small><h3>{{ $batch->valid_rows }}</h3></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted">Importadas</small><h3>{{ $batch->imported_rows }}</h3></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted">Duplicadas</small><h3>{{ $batch->duplicate_rows }}</h3></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted">Erros</small><h3>{{ $batch->error_rows }}</h3></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted">Status</small><h5>{{ $batch->status }}</h5></div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Usuário:</strong> {{ optional($batch->user)->name ?? '-' }}</div>
                <div class="col-md-4"><strong>Início:</strong> {{ optional($batch->started_at)->format('d/m/Y H:i') ?? '-' }}</div>
                <div class="col-md-4"><strong>Fim:</strong> {{ optional($batch->finished_at)->format('d/m/Y H:i') ?? '-' }}</div>
            </div>
            @if($batch->notes)
                <hr>
                <strong>Observações:</strong>
                <div class="text-muted">{{ $batch->notes }}</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Linhas registradas</strong>
            <span class="text-muted">— linhas importadas com sucesso podem ficar sem raw_data para evitar duplicidade no banco.</span>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Linha</th>
                        <th>Status</th>
                        <th>Mensagem</th>
                        <th>Questão criada</th>
                        <th>Possível duplicada</th>
                        <th>Enunciado normalizado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->row_number }}</td>
                            <td><span class="badge bg-secondary">{{ $row->status }}</span></td>
                            <td style="max-width: 360px; white-space: normal;">{{ $row->error_message ?? '-' }}</td>
                            <td>
                                @if($row->created_question_id)
                                    <a href="{{ route('admin.questions.edit', $row->created_question_id) }}">#{{ $row->created_question_id }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($row->duplicate_question_id)
                                    <a href="{{ route('admin.questions.edit', $row->duplicate_question_id) }}">#{{ $row->duplicate_question_id }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="max-width: 420px; white-space: normal;">{{ $row->normalized_statement ? \Illuminate\Support\Str::limit($row->normalized_statement, 160) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma linha registrada para este lote.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div class="card-footer">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
