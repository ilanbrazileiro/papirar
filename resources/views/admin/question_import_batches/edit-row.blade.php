@extends('layouts.admin')

@section('title', 'Conferir questão extraída')

@section('content')
@php
    $raw = (array) $row->raw_data;
    $alternatives = (array) ($raw['alternatives'] ?? []);
    $meta = (array) ($raw['_meta'] ?? []);
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Conferir questão {{ $row->row_number }}</h1>
            <p class="text-muted mb-0">Lote #{{ $batch->id }} · página {{ $meta['page_number'] ?? 'não identificada' }}</p>
        </div>
        <a href="{{ route('admin.question-import-batches.review', $batch) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.question-import-batches.rows.update', [$batch, $row]) }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header"><strong>Classificação</strong></div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Disciplina</label>
                    <select name="subject_id" id="subject_id" class="form-control" required>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $raw['subject_id'] ?? '') === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tópico</label>
                    <select name="topic_id" id="topic_id" class="form-control">
                        <option value="">Sem tópico</option>
                        @foreach($subjects as $subject)
                            @foreach($subject->topics as $topic)
                                <option value="{{ $topic->id }}" data-subject="{{ $subject->id }}" @selected((string) old('topic_id', $raw['topic_id'] ?? '') === (string) $topic->id)>{{ $topic->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Confiança da IA: {{ isset($meta['classification_confidence']) ? number_format($meta['classification_confidence'] * 100, 0).'%': '-' }}</small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Conteúdo fiel ao documento</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Enunciado</label>
                    <textarea name="statement" rows="10" class="form-control" required>{{ old('statement', $raw['statement'] ?? '') }}</textarea>
                </div>
                @foreach(['A', 'B', 'C', 'D', 'E'] as $letter)
                    <div class="mb-3">
                        <label class="form-label">Alternativa {{ $letter }}</label>
                        <textarea name="alternatives[{{ $letter }}]" rows="2" class="form-control" required>{{ old('alternatives.'.$letter, $alternatives[$letter] ?? $raw['alternative_'.strtolower($letter)] ?? '') }}</textarea>
                    </div>
                @endforeach
                <div class="col-md-3 px-0">
                    <label class="form-label">Gabarito</label>
                    <select name="correct_letter" class="form-control" required>
                        @foreach(['A', 'B', 'C', 'D', 'E'] as $letter)
                            <option value="{{ $letter }}" @selected(old('correct_letter', $raw['correct_letter'] ?? '') === $letter)>{{ $letter }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button class="btn btn-primary">Salvar e revalidar</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subject = document.getElementById('subject_id');
    const topic = document.getElementById('topic_id');
    function filterTopics() {
        const selected = subject.value;
        Array.from(topic.options).forEach(function (option) {
            if (!option.value) return;
            option.hidden = option.dataset.subject !== selected;
            if (option.hidden && option.selected) topic.value = '';
        });
    }
    subject.addEventListener('change', filterTopics);
    filterTopics();
});
</script>
@endpush
