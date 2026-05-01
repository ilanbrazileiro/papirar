@csrf

<input type="hidden" name="question_type" value="multiple_choice">

@php
    $letters = ['A', 'B', 'C', 'D', 'E'];
    $alts = $question->alternatives->sortBy('letter')->values();
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Corporação</label>
        <select name="corporation_id" id="corporation_id" class="form-select">
            <option value="">Selecione</option>
            <option value="">Questão geral / sem corporação específica</option>
            @foreach($corporations as $corporation)
                <option value="{{ $corporation->id }}" @selected(old('corporation_id', $question->corporation_id) == $corporation->id)>
                    {{ $corporation->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Disciplina</label>
        <select name="subject_id" id="subject_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $question->subject_id) == $subject->id)>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Assunto</label>
        <select name="topic_id" id="topic_id" class="form-select">
            @if($selectedTopic)
                <option value="{{ $selectedTopic->id }}" selected>{{ $selectedTopic->name }}</option>
            @endif
        </select>
        <div class="form-text">Busca dinâmica por disciplina.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Concurso</label>
        <select name="exam_id" id="exam_id" class="form-select">
            @if($selectedExam)
                <option value="{{ $selectedExam->id }}" selected>{{ $selectedExam->title }} ({{ $selectedExam->year }}) - {{ $selectedExam->exam_type }}</option>
            @endif
        </select>
        <div class="form-text">Busca dinâmica por corporação.</div>
    </div>

    <div class="col-md-2">
        <label class="form-label">Dificuldade</label>
        <select name="difficulty" class="form-select" required>
            <option value="easy" @selected(old('difficulty', $question->difficulty) === 'easy')>Fácil</option>
            <option value="medium" @selected(old('difficulty', $question->difficulty) === 'medium')>Média</option>
            <option value="hard" @selected(old('difficulty', $question->difficulty) === 'hard')>Difícil</option>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Origem</label>
        <select name="source_type" class="form-select" required>
            <option value="official_exam" @selected(old('source_type', $question->source_type) === 'exam')>Prova oficial</option>
            <option value="authored" @selected(old('source_type', $question->source_type) === 'authored')>Autoral</option>
            <option value="adapted" @selected(old('source_type', $question->source_type) === 'adapted')>Adaptada</option>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="draft" @selected(old('status', $question->status) === 'draft')>Rascunho</option>
            <option value="published" @selected(old('status', $question->status) === 'published')>Publicada</option>
            <option value="archived" @selected(old('status', $question->status) === 'archived')>Arquivada</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Referência da fonte</label>
        <input type="text" name="source_reference" class="form-control" value="{{ old('source_reference', $question->source_reference) }}" placeholder="Ex.: PMERJ 2024 / questão 12">
    </div>

    <div class="col-12">
        <label class="form-label">Enunciado</label>
        <textarea name="statement" rows="8" class="form-control" required>{{ old('statement', $question->statement) }}</textarea>
    </div>
</div>

<div class="panel p-4 mt-4">
    <div class="fw-bold mb-3">Alternativas</div>
    <div class="row g-3">
        @foreach($letters as $index => $letter)
            @php
                $alt = $alts->firstWhere('letter', $letter);
                $oldText = old("alternatives.$index.text", $alt->text ?? '');
                $oldLetter = old("alternatives.$index.letter", $letter);
                $correctLetter = old('correct_letter', optional($question->alternatives->firstWhere('is_correct', true))->letter ?? 'A');
            @endphp
            <div class="col-12">
                <div class="border rounded-4 p-3">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-1">
                            <label class="form-label">Letra</label>
                            <input type="text" class="form-control" value="{{ $letter }}" readonly>
                            <input type="hidden" name="alternatives[{{ $index }}][letter]" value="{{ $oldLetter }}">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Texto da alternativa {{ $letter }}</label>
                            <textarea name="alternatives[{{ $index }}][text]" rows="3" class="form-control" required>{{ $oldText }}</textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Correta?</label>
                            <div class="border rounded-3 px-3 py-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="correct_letter" value="{{ $letter }}" id="correct_{{ $letter }}" @checked($correctLetter === $letter)>
                                    <label class="form-check-label" for="correct_{{ $letter }}">Marcar {{ $letter }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="panel p-4 mt-4">
    <label class="form-label fw-bold">Comentário / gabarito comentado</label>
    <textarea name="commented_answer" rows="8" class="form-control">{{ old('commented_answer', $question->commented_answer) }}</textarea>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary">{{ $submitLabel }}</button>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container .select2-selection--single {
    height: 38px;
    border: 1px solid #dee2e6;
    border-radius: .375rem;
    padding-top: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('#exam_id').select2({
        width: '100%',
        placeholder: 'Selecione ou pesquise um concurso',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.questions.ajax.exams') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, corporation_id: $('#corporation_id').val() };
            },
            processResults: function (data) { return data; }
        }
    });

    $('#topic_id').select2({
        width: '100%',
        placeholder: 'Selecione ou pesquise um assunto',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.questions.ajax.topics') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, subject_id: $('#subject_id').val() };
            },
            processResults: function (data) { return data; }
        }
    });

    $('#corporation_id').on('change', function () {
        $('#exam_id').val(null).trigger('change');
    });

    $('#subject_id').on('change', function () {
        $('#topic_id').val(null).trigger('change');
    });
});
</script>
@endpush
