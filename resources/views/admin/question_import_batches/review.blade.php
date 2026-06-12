@extends('layouts.admin')

@section('title', 'Revisar importação de questões')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Revisar importação</h1>
            <p class="text-muted mb-0">Confira as linhas válidas, duplicadas e com erro antes de gravar as questões.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.question-import-batches.index') }}" class="btn btn-outline-secondary">Importações</a>
            <a href="{{ route('admin.questions.import.create') }}" class="btn btn-outline-primary">Novo CSV</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Arquivo</strong><br>
                    <span class="text-muted">{{ $batch->original_filename ?: $batch->filename }}</span>
                </div>
                <div class="col-md-2">
                    <strong>Total</strong><br>
                    <span class="badge bg-secondary">{{ $batch->total_rows }}</span>
                </div>
                <div class="col-md-2">
                    <strong>Válidas</strong><br>
                    <span class="badge bg-success">{{ $batch->valid_rows }}</span>
                </div>
                <div class="col-md-2">
                    <strong>Duplicadas</strong><br>
                    <span class="badge bg-warning text-dark">{{ $batch->duplicate_rows }}</span>
                </div>
                <div class="col-md-2">
                    <strong>Erros</strong><br>
                    <span class="badge bg-danger">{{ $batch->error_rows }}</span>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.question-import-batches.confirm', $batch) }}" id="confirm-import-form">
        @csrf

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Linhas do CSV</strong>
                    <span class="text-muted small">As questões importadas entrarão sempre como rascunho.</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="select-valid-rows">Selecionar válidas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-valid-rows">Limpar</button>
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirmar a importação das linhas válidas selecionadas como rascunho?')">
                        Importar selecionadas
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th style="width: 80px;">Linha</th>
                            <th style="width: 120px;">Status</th>
                            <th>Enunciado / Mensagem</th>
                            <th style="width: 220px;">Referência</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batch->rows as $row)
                            @php
                                $raw = is_array($row->raw_data) ? $row->raw_data : [];
                                $statement = $raw['statement'] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    @if($row->status === 'valid')
                                        <input type="checkbox" name="row_ids[]" value="{{ $row->id }}" class="form-check-input valid-row-checkbox" checked>
                                    @endif
                                </td>
                                <td>{{ $row->row_number }}</td>
                                <td>
                                    @if($row->status === 'valid')
                                        <span class="badge bg-success">Válida</span>
                                    @elseif($row->status === 'duplicate')
                                        <span class="badge bg-warning text-dark">Duplicada</span>
                                    @elseif($row->status === 'error')
                                        <span class="badge bg-danger">Erro</span>
                                    @elseif($row->status === 'imported')
                                        <span class="badge bg-primary">Importada</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $row->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($statement)
                                        <div class="fw-semibold">{{ \Illuminate\Support\Str::limit(strip_tags($statement), 180) }}</div>
                                    @endif

                                    @if($row->error_message)
                                        <div class="text-muted small mt-1">{{ $row->error_message }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($row->duplicate_question_id)
                                        <a href="{{ route('admin.questions.edit', $row->duplicate_question_id) }}" class="btn btn-sm btn-outline-warning">
                                            Ver duplicada #{{ $row->duplicate_question_id }}
                                        </a>
                                    @elseif($row->created_question_id)
                                        <a href="{{ route('admin.questions.edit', $row->created_question_id) }}" class="btn btn-sm btn-outline-primary">
                                            Ver questão #{{ $row->created_question_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhuma linha encontrada neste lote.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    @if(!in_array($batch->status, ['imported', 'partial'], true))
        <form method="POST" action="{{ route('admin.question-import-batches.cancel', $batch) }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Cancelar este lote? Nenhuma questão será importada.')">
                Cancelar lote
            </button>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectBtn = document.getElementById('select-valid-rows');
    const clearBtn = document.getElementById('clear-valid-rows');

    if (selectBtn) {
        selectBtn.addEventListener('click', function () {
            document.querySelectorAll('.valid-row-checkbox').forEach((checkbox) => checkbox.checked = true);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            document.querySelectorAll('.valid-row-checkbox').forEach((checkbox) => checkbox.checked = false);
        });
    }
});
</script>
@endpush
