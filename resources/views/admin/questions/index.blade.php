@extends('layouts.admin')

@section('title', $isDraftPage ?? false ? 'Questões em rascunho' : 'Questões')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">{{ $isDraftPage ?? false ? 'Questões em rascunho' : 'Questões' }}</h1>
            <p class="text-muted mb-0">
                {{ $isDraftPage ?? false ? 'Revise e publique questões ainda não liberadas para os alunos.' : 'Gerencie as questões com filtros, vínculos e status de publicação.' }}
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova questão
            </a>
            <a href="{{ route('admin.questions.drafts') }}" class="btn btn-danger">
                <i class="fas fa-edit"></i> Rascunhos
            </a>
            <a href="{{ route('admin.questions.index') }}?status=reviewed" class="btn btn-success">
                <i class="fas fa-check-circle"></i> Revisadas
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

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ $isDraftPage ?? false ? route('admin.questions.drafts') : route('admin.questions.index') }}" class="row g-3 align-items-end">
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
                @unless($isDraftPage ?? false)
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
                @endunless
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
                        <select name="status" class="form-control form-control-sm" style="width: 200px;">
                            <option value="">Alterar status...</option>
                            <option value="draft">Marcar como rascunho</option>
                            <option value="published">Publicar</option>
                            <option value="reviewed">Marcar como revisada</option>
                            <option value="archived">Arquivar</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirmBulkStatusChange();">
                            Aplicar nas selecionadas
                        </button>
                    </div>
                    <small class="text-muted">Selecione uma ou mais questões na tabela abaixo.</small>
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
                                <th>Corporação</th>
                                <th>Disciplina</th>
                                <th>Concurso</th>
                                <th>Fonte</th>
                                <th>Dificuldade</th>
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
                                    <td>{{ $question->corporation->name ?? '-' }}</td>
                                    <td>{{ $question->subject->name ?? '-' }}</td>
                                    <td>{{ $question->exam->title ?? '-' }}</td>
                                    <td>
                                        @if($question->sourceMaterial)
                                            <span title="{{ $question->sourceMaterial->title }}">{{ \Illuminate\Support\Str::limit($question->sourceMaterial->title, 35) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($question->difficulty) }}</td>
                                    <td>
                                        @if($question->status === 'published')
                                            <span class="badge bg-info text-dark">Publicada</span>
                                        @elseif($question->status === 'reviewed')
                                            <span class="badge bg-success">Revisada</span>
                                        @elseif($question->status === 'draft')
                                            <span class="badge bg-danger">Rascunho</span>
                                        @else
                                            <span class="badge bg-secondary">Arquivada</span>
                                        @endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($question->statement), 120) }}</td>
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

        if (!selected) {
            alert('Selecione pelo menos uma questão.');
            return false;
        }

        if (!status) {
            alert('Selecione o status desejado.');
            return false;
        }

        return confirm('Deseja alterar o status das questões selecionadas?');
    }
</script>
@endpush
