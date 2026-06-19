@extends('layouts.admin')

@section('title', 'Questões similares')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Questões similares</h1>
                <p class="text-muted mb-0">Encontre possíveis duplicidades ou questões muito parecidas antes de publicar ou revisar.</p>
            </div>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                Voltar para questões
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Buscar similaridade</strong>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.questions.similar.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ID da questão</label>
                        <input type="number" name="question_id" class="form-control" value="{{ old('question_id', $filters['question_id'] ?? '') }}" placeholder="Ex.: 1339">
                        <small class="text-muted">Use para comparar uma questão existente.</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Escopo</label>
                        <select name="scope" class="form-control">
                            <option value="same_subject" @selected(($filters['scope'] ?? 'same_subject') === 'same_subject')>Mesma disciplina</option>
                            <option value="same_topic" @selected(($filters['scope'] ?? '') === 'same_topic')>Mesmo tópico</option>
                            <option value="all" @selected(($filters['scope'] ?? '') === 'all')>Todas as questões</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Similaridade mínima</label>
                        <input type="number" name="min_score" class="form-control" min="20" max="100" value="{{ old('min_score', $filters['min_score'] ?? 65) }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Rascunho</option>
                            <option value="published" @selected(($filters['status'] ?? '') === 'published')>Publicada</option>
                            <option value="reviewed" @selected(($filters['status'] ?? '') === 'reviewed')>Revisada</option>
                            <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Arquivada</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Analisar</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Filtrar disciplina</label>
                        <select name="subject_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string)($filters['subject_id'] ?? '') === (string)$subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Filtrar tópico</label>
                        <select name="topic_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" @selected((string)($filters['topic_id'] ?? '') === (string)$topic->id)>{{ optional($topic->subject)->name ? optional($topic->subject)->name . ' - ' : '' }}{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ou cole um enunciado para comparar</label>
                    <textarea name="text" rows="5" class="form-control" placeholder="Cole aqui o enunciado da questão...">{{ old('text', $filters['text'] ?? '') }}</textarea>
                </div>
            </form>
        </div>
    </div>

    @if($baseQuestion)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                Questão base #{{ $baseQuestion->id }}
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-secondary">{{ $baseQuestion->status }}</span>
                    <span class="text-muted">{{ optional($baseQuestion->subject)->name }} @if($baseQuestion->topic) / {{ $baseQuestion->topic->name }} @endif</span>
                </div>
                <div>{!! $baseQuestion->statement !!}</div>
                <div class="mt-3">
                    <a href="{{ route('admin.questions.edit', $baseQuestion) }}" class="btn btn-sm btn-outline-primary">Editar questão base</a>
                </div>
            </div>
        </div>
    @endif

    @if($searched)
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Resultado da análise</strong>
                <span class="badge bg-info">{{ count($results) }} possível(is) similar(es)</span>
            </div>
            <div class="card-body">
                @forelse($results as $item)
                    @php($question = $item['question'])
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>#{{ $question->id }}</strong>
                                <span class="badge bg-secondary ms-2">{{ $question->status }}</span>
                                <span class="text-muted ms-2">{{ optional($question->subject)->name }} @if($question->topic) / {{ $question->topic->name }} @endif</span>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $item['score'] >= 85 ? 'bg-danger' : ($item['score'] >= 70 ? 'bg-warning text-dark' : 'bg-info') }}">
                                    {{ $item['score'] }}% similar
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">{!! Str::limit(strip_tags($question->statement), 420) !!}</div>

                        @if(!empty($item['common_terms']))
                            <div class="mb-2">
                                <small class="text-muted">Termos em comum:</small>
                                @foreach($item['common_terms'] as $term)
                                    <span class="badge bg-light text-dark border">{{ $term }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="{{ route('admin.questions.similar.index', ['question_id' => $question->id]) }}" class="btn btn-sm btn-outline-secondary">Analisar esta</a>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-success mb-0">
                        Nenhuma questão similar encontrada com os filtros informados.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
