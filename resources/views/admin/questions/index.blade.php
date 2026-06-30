@extends('layouts.admin')

@php
    $currentStatus = $status ?? '';
    $pageTitle = match ($currentStatus) {
        'draft' => 'Questões em rascunho',
        'published' => 'Questões publicadas',
        'reviewed' => 'Questões revisadas',
        'archived' => 'Questões arquivadas',
        default => 'Controle editorial de questões',
    };

    $statusBadgeClass = function (?string $status) {
        return match ($status) {
            'draft' => 'bg-danger',
            'published' => 'bg-warning text-dark',
            'reviewed' => 'bg-success',
            'archived' => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    };
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1">{{ $pageTitle }}</h1>
            <p class="text-muted mb-0">
                Controle o ciclo editorial: rascunho não aparece, publicada aparece e aguarda revisão, revisada aparece e já foi validada, arquivada sai da área do aluno.
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova questão
            </a>
            <a href="{{ route('admin.questions.index', ['status' => 'published']) }}" class="btn btn-warning">
                <i class="fas fa-eye"></i> Pendentes de revisão
            </a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Todas
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-3">
        @foreach($statusCards as $card)
            <div class="col-xl col-md-4 col-sm-6">
                <a href="{{ $card['url'] }}" class="text-decoration-none">
                    <div class="card h-100 border-{{ $currentStatus === $card['key'] ? $card['class'] : 'light' }} shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="text-muted small">{{ $card['title'] }}</div>
                                    <div class="h3 mb-1 text-dark">{{ number_format($card['count'], 0, ',', '.') }}</div>
                                </div>
                                <span class="badge bg-{{ $card['class'] }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>
                            </div>
                            <p class="small text-muted mb-0">{{ $card['description'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4">
                    <h5 class="mb-1">Revisão editorial</h5>
                    <p class="text-muted mb-0">
                        {{ number_format($pendingReviewCount ?? 0, 0, ',', '.') }} questão(ões) publicadas ainda precisam ser revisadas.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Questões visíveis já revisadas</span>
                        <strong>{{ $reviewProgress ?? 0 }}%</strong>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $reviewProgress ?? 0 }}%;" aria-valuenow="{{ $reviewProgress ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">
                        Base: publicadas + revisadas visíveis para o aluno.
                    </small>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <a href="{{ route('admin.questions.index', ['status' => 'published']) }}" class="btn btn-outline-warning">
                        Revisar pendentes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.questions.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Enunciado, fonte ou referência">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Corporação</label>
                    <select name="corporation_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((int)($corporationId ?? 0) === (int)$corporation->id)>{{ $corporation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Disciplina</label>
                    <select name="subject_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((int)($subjectId ?? 0) === (int)$subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fonte / Bibliografia</label>
                    <select name="source_material_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($sourceMaterials as $material)
                            <option value="{{ $material->id }}" @selected((int)($sourceMaterialId ?? 0) === (int)$material->id)>{{ $material->title }}</option>
                        @endforeach
                    </select>
                </div>
               
                <div class="col-md-2">
                    <label class="form-label">Banca</label>
                    <select name="exam_board_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($examBoards as $examBoard)
                            <option value="{{ $examBoard->id }}" @selected((int)($examBoardId ?? 0) === (int)$examBoard->id)>
                                {{ $examBoard->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="draft" @selected(($status ?? '') === 'draft')>Rascunho</option>
                        <option value="published" @selected(($status ?? '') === 'published')>Publicada</option>
                        <option value="reviewed" @selected(($status ?? '') === 'reviewed')>Revisada</option>
                        <option value="archived" @selected(($status ?? '') === 'archived')>Arquivada</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Dificuldade</label>
                    <select name="difficulty" class="form-control">
                        <option value="">Todas</option>
                        <option value="easy" @selected(($difficulty ?? '') === 'easy')>Fácil</option>
                        <option value="medium" @selected(($difficulty ?? '') === 'medium')>Média</option>
                        <option value="hard" @selected(($difficulty ?? '') === 'hard')>Difícil</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    @if($questions->count())
        <form method="POST" action="{{ route('admin.questions.bulk-status') }}" id="bulk-status-form">
            @csrf
            @method('PATCH')

            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <strong>Ações em lote:</strong>
                        <select name="status" class="form-control form-control-sm" style="width: 260px;">
                            <option value="">Alterar status...</option>
                            <option value="draft">Voltar para rascunho — não aparece</option>
                            <option value="published">Publicar — aparece e aguarda revisão</option>
                            <option value="reviewed">Marcar como revisada — aparece e validada</option>
                            <option value="archived">Arquivar — não aparece</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkStatusChange();">
                            Aplicar nas selecionadas
                        </button>
                    </div>
                    <small class="text-muted">Use “Publicada” como pendente de revisão; use “Revisada” após validação editorial.</small>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="select-all-questions">
                                </th>
                                <th>ID</th>
                                <th>Disciplina</th>
                                <th>Tópico</th>
                                <th>Concurso</th>
                                <th>Banca</th>
                                <th>Status</th>
                                <th>Enunciado</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" class="form-check-input question-checkbox">
                                    </td>
                                    <td>#{{ $question->id }}</td>
                                    <td>
                                        <div>{{ $question->subject->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $question->corporation->name ?? 'Sem corporação' }}</small>
                                    </td>
                                    <td>{{ $question->topic->name ?? '-' }}</td>
                                    <td>{{ $question->exam->title ?? '-' }}</td>
                                    <td>{{ $question->examBoard->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $statusBadgeClass($question->status) }}">
                                            {{ $question->status_label }}
                                        </span>
                                        @if($question->status === 'published')
                                            <div><small class="text-warning">Pendente de revisão</small></div>
                                        @elseif($question->status === 'reviewed')
                                            <div><small class="text-success">Validada</small></div>
                                        @elseif($question->status === 'draft')
                                            <div><small class="text-muted">Oculta para aluno</small></div>
                                        @elseif($question->status === 'archived')
                                            <div><small class="text-muted">Oculta para aluno</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ \Illuminate\Support\Str::limit(strip_tags($question->statement), 120) }}</div>
                                        <small class="text-muted">Dificuldade: {{ ucfirst($question->difficulty) }}</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir esta questão?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <div class="mt-3">
            {{ $questions->links() }}
        </div>
    @else
        <div class="alert alert-info">
            Nenhuma questão encontrada.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all-questions');
        const checkboxes = document.querySelectorAll('.question-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        }
    });

    function confirmBulkStatusChange() {
        const selected = document.querySelectorAll('.question-checkbox:checked').length;
        const status = document.querySelector('#bulk-status-form select[name="status"]')?.value;
        const labels = {
            draft: 'voltar para rascunho. Elas não aparecerão para o aluno',
            published: 'publicar. Elas aparecerão para o aluno e ficarão pendentes de revisão',
            reviewed: 'marcar como revisadas. Elas aparecerão para o aluno como validadas',
            archived: 'arquivar. Elas deixarão de aparecer para o aluno'
        };

        if (!selected) {
            alert('Selecione pelo menos uma questão.');
            return false;
        }

        if (!status) {
            alert('Selecione o status desejado.');
            return false;
        }

        return confirm('Deseja ' + labels[status] + '?');
    }
</script>
@endpush
