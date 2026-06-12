@extends('layouts.admin')

@section('title', 'Revisar importação de questões')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h3 mb-1">Revisar importação</h1>
            <p class="text-muted mb-0">Confira as linhas do CSV antes de gravar as questões. As questões importadas entram sempre como rascunho.</p>
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

    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="small-box bg-secondary mb-0">
                <div class="inner">
                    <h3>{{ $batch->total_rows }}</h3>
                    <p>Total de linhas</p>
                </div>
                <div class="icon"><i class="fas fa-file-csv"></i></div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="small-box bg-success mb-0">
                <div class="inner"><h3>{{ $batch->valid_rows }}</h3><p>Válidas</p></div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="small-box bg-primary mb-0">
                <div class="inner"><h3>{{ $batch->imported_rows }}</h3><p>Importadas</p></div>
                <div class="icon"><i class="fas fa-cloud-upload-alt"></i></div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="small-box bg-warning mb-0">
                <div class="inner"><h3>{{ $batch->duplicate_rows }}</h3><p>Duplicadas</p></div>
                <div class="icon"><i class="fas fa-copy"></i></div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="small-box bg-danger mb-0">
                <div class="inner"><h3>{{ $batch->error_rows }}</h3><p>Erros</p></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <strong>Arquivo</strong><br>
                    <span class="text-muted">{{ $batch->original_filename ?: $batch->filename }}</span>
                </div>
                <div class="col-md-3">
                    <label for="status-filter" class="form-label">Filtrar linhas</label>
                    <select id="status-filter" class="form-control" onchange="if (this.value) { window.location = this.value; }">
                        <option value="{{ route('admin.question-import-batches.review', $batch) }}" {{ empty($statusFilter) ? 'selected' : '' }}>Todas as linhas</option>
                        <option value="{{ route('admin.question-import-batches.review', [$batch, 'status' => 'valid']) }}" {{ $statusFilter === 'valid' ? 'selected' : '' }}>Válidas ({{ (int) ($statusCounts['valid'] ?? 0) }})</option>
                        <option value="{{ route('admin.question-import-batches.review', [$batch, 'status' => 'duplicate']) }}" {{ $statusFilter === 'duplicate' ? 'selected' : '' }}>Duplicadas ({{ (int) ($statusCounts['duplicate'] ?? 0) }})</option>
                        <option value="{{ route('admin.question-import-batches.review', [$batch, 'status' => 'error']) }}" {{ $statusFilter === 'error' ? 'selected' : '' }}>Erros ({{ (int) ($statusCounts['error'] ?? 0) }})</option>
                        <option value="{{ route('admin.question-import-batches.review', [$batch, 'status' => 'imported']) }}" {{ $statusFilter === 'imported' ? 'selected' : '' }}>Importadas ({{ (int) ($statusCounts['imported'] ?? 0) }})</option>
                        <option value="{{ route('admin.question-import-batches.review', [$batch, 'status' => 'ignored']) }}" {{ $statusFilter === 'ignored' ? 'selected' : '' }}>Ignoradas ({{ (int) ($statusCounts['ignored'] ?? 0) }})</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <span class="badge badge-light border">Status do lote: {{ $batch->status }}</span>
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
                    <span class="text-muted small d-block">Você pode importar todas as válidas, selecionar algumas ou tratar linha por linha.</span>
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
                            <th style="width: 280px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
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
                                    @switch($row->status)
                                        @case('valid')
                                            <span class="badge badge-success">Válida</span>
                                            @break
                                        @case('duplicate')
                                            <span class="badge badge-warning">Duplicada</span>
                                            @break
                                        @case('error')
                                            <span class="badge badge-danger">Erro</span>
                                            @break
                                        @case('imported')
                                            <span class="badge badge-primary">Importada</span>
                                            @break
                                        @case('ignored')
                                            <span class="badge badge-secondary">Ignorada</span>
                                            @break
                                        @default
                                            <span class="badge badge-light">{{ $row->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($statement)
                                        <div class="font-weight-bold">{{ \Illuminate\Support\Str::limit(strip_tags($statement), 220) }}</div>
                                    @elseif($row->created_question_id)
                                        <div class="text-muted">Linha importada com sucesso. Conteúdo bruto removido para evitar duplicidade no banco.</div>
                                    @else
                                        <div class="text-muted">Sem enunciado disponível.</div>
                                    @endif

                                    @if($row->error_message)
                                        <div class="text-muted small mt-1">{{ $row->error_message }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($row->status === 'valid')
                                            <button type="submit"
                                                formaction="{{ route('admin.question-import-batches.rows.import', [$batch, $row]) }}"
                                                formmethod="POST"
                                                class="btn btn-outline-success"
                                                onclick="return confirm('Importar apenas a linha {{ $row->row_number }} como rascunho?')">
                                                Importar
                                            </button>
                                            <button type="submit"
                                                formaction="{{ route('admin.question-import-batches.rows.ignore', [$batch, $row]) }}"
                                                formmethod="POST"
                                                class="btn btn-outline-secondary"
                                                onclick="return confirm('Ignorar a linha {{ $row->row_number }}?')">
                                                Ignorar
                                            </button>
                                        @elseif(in_array($row->status, ['duplicate', 'error'], true))
                                            @if($row->duplicate_question_id)
                                                <a href="{{ route('admin.questions.edit', $row->duplicate_question_id) }}" class="btn btn-outline-warning">
                                                    Ver duplicada #{{ $row->duplicate_question_id }}
                                                </a>
                                            @endif
                                            <button type="submit"
                                                formaction="{{ route('admin.question-import-batches.rows.ignore', [$batch, $row]) }}"
                                                formmethod="POST"
                                                class="btn btn-outline-secondary"
                                                onclick="return confirm('Ignorar a linha {{ $row->row_number }}?')">
                                                Ignorar
                                            </button>
                                        @elseif($row->created_question_id)
                                            <a href="{{ route('admin.questions.edit', $row->created_question_id) }}" class="btn btn-outline-primary">
                                                Ver questão #{{ $row->created_question_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhuma linha encontrada para este filtro.</td>
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
    </form>

    @if(!in_array($batch->status, ['imported', 'partial', 'partial_imported'], true))
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
