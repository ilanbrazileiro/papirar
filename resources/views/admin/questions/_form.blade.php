@php
    $isEdit = isset($question) && $question && $question->exists;
    $formAction = $formAction ?? ($isEdit ? route('admin.questions.update', $question) : route('admin.questions.store'));
    $formMethod = strtoupper($formMethod ?? ($isEdit ? 'PUT' : 'POST'));
    $submitLabel = $submitLabel ?? ($isEdit ? 'Salvar alterações' : 'Salvar questão');
    $letters = ['A', 'B', 'C', 'D', 'E'];
    $alts = $question->alternatives ?? collect();
    $alts = $alts->sortBy('letter')->values();
    $correctLetter = old('correct_letter', optional($alts->firstWhere('is_correct', true))->letter ?? 'A');
@endphp

@csrf
@if($formMethod !== 'POST')
    @method($formMethod)
@endif

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Enunciado da questão</h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">
            Para inserir imagem, clique no botão de imagem do editor. O arquivo será salvo no Storage e inserido no enunciado automaticamente.
        </p>

        <div class="mb-3">
            <label for="statement" class="form-label">Enunciado *</label>
            <textarea name="statement" id="statement" rows="8" class="form-control papirar-rich-editor @error('statement') is-invalid @enderror">{{ old('statement', $question->statement ?? '') }}</textarea>
            @error('statement')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Alternativas</h3>
    </div>
    <div class="card-body">
        @foreach($letters as $index => $letter)
            @php
                $alt = $alts->firstWhere('letter', $letter);
                $oldText = old("alternatives.$index.text", $alt->text ?? '');
                $oldLetter = old("alternatives.$index.letter", $letter);
            @endphp

            <div class="border rounded p-3 mb-3">
                <div class="row align-items-start">
                    <div class="col-md-2">
                        <label class="form-label">Letra {{ $letter }}</label>
                        <input type="text" name="alternatives[{{ $index }}][letter]" class="form-control" value="{{ $oldLetter }}" readonly>
                    </div>
                    <div class="col-md-8">
                        <label for="alternative_{{ $letter }}" class="form-label">Texto da alternativa {{ $letter }} *</label>
                        <textarea name="alternatives[{{ $index }}][text]" id="alternative_{{ $letter }}" rows="3" class="form-control editor-small papirar-rich-editor @error("alternatives.$index.text") is-invalid @enderror">{{ $oldText }}</textarea>
                        @error("alternatives.$index.text")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 pt-4">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="correct_letter" id="correct_{{ $letter }}" value="{{ $letter }}" @checked($correctLetter === $letter)>
                            <label class="form-check-label" for="correct_{{ $letter }}">Correta</label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @error('correct_letter')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Comentário / gabarito comentado</h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="commented_answer" class="form-label">Comentário</label>
            <textarea name="commented_answer" id="commented_answer" rows="8" class="form-control editor papirar-rich-editor @error('commented_answer') is-invalid @enderror">{{ old('commented_answer', $question->commented_answer ?? '') }}</textarea>
            @error('commented_answer')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Configurações</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="corporation_id" class="form-label">Corporação</label>
                <select name="corporation_id" id="corporation_id" class="form-control @error('corporation_id') is-invalid @enderror">
                    <option value="">Questão geral / sem corporação específica</option>
                    @foreach($corporations as $corporation)
                        <option value="{{ $corporation->id }}" @selected((string) old('corporation_id', $question->corporation_id ?? '') === (string) $corporation->id)>
                            {{ $corporation->name }}
                        </option>
                    @endforeach
                </select>
                @error('corporation_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="subject_id" class="form-label">Disciplina *</label>
                <select name="subject_id" id="subject_id" class="form-control @error('subject_id') is-invalid @enderror">
                    <option value="">Selecione</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) old('subject_id', $question->subject_id ?? '') === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="topic_id" class="form-label">Assunto</label>
                <select name="topic_id" id="topic_id" class="form-control @error('topic_id') is-invalid @enderror" data-placeholder="Selecione o assunto">
                    <option value="">Selecione uma disciplina primeiro</option>
                    @if(old('topic_id', $question->topic_id ?? null) && isset($selectedTopic) && $selectedTopic)
                        <option value="{{ $selectedTopic->id }}" selected>{{ $selectedTopic->name }}</option>
                    @endif
                </select>
                <small class="text-muted">Busca dinâmica por disciplina.</small>
                @error('topic_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="exam_id" class="form-label">Concurso / prova de origem</label>
                <select name="exam_id" id="exam_id" class="form-control @error('exam_id') is-invalid @enderror" data-placeholder="Selecione o concurso/prova">
                    <option value="">Sem prova de origem</option>
                    @if(old('exam_id', $question->exam_id ?? null) && isset($selectedExam) && $selectedExam)
                        <option value="{{ $selectedExam->id }}" selected>
                            {{ $selectedExam->title }} ({{ $selectedExam->year }}) - {{ $selectedExam->exam_type }}
                        </option>
                    @endif
                </select>
                <small class="text-muted">Use apenas quando a questão veio de prova oficial.</small>
                @error('exam_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @include('admin.questions.snippets._source_material_select')

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="difficulty" class="form-label">Dificuldade</label>
                <select name="difficulty" id="difficulty" class="form-control @error('difficulty') is-invalid @enderror">
                    <option value="easy" @selected(old('difficulty', $question->difficulty ?? 'medium') === 'easy')>Fácil</option>
                    <option value="medium" @selected(old('difficulty', $question->difficulty ?? 'medium') === 'medium')>Média</option>
                    <option value="hard" @selected(old('difficulty', $question->difficulty ?? 'medium') === 'hard')>Difícil</option>
                </select>
                @error('difficulty')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="source_type" class="form-label">Origem</label>
                <select name="source_type" id="source_type" class="form-control @error('source_type') is-invalid @enderror">
                    <option value="exam" @selected(old('source_type', $question->source_type ?? 'authored') === 'exam')>Prova oficial</option>
                    <option value="authored" @selected(old('source_type', $question->source_type ?? 'authored') === 'authored')>Autoral</option>
                    <option value="adapted" @selected(old('source_type', $question->source_type ?? 'authored') === 'adapted')>Adaptada</option>
                </select>
                @error('source_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="draft" @selected(old('status', $question->status ?? 'draft') === 'draft')>Rascunho</option>
                    <option value="published" @selected(old('status', $question->status ?? 'draft') === 'published')>Publicada</option>
                    <option value="archived" @selected(old('status', $question->status ?? 'draft') === 'archived')>Arquivada</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="source_reference" class="form-label">Referência da fonte</label>
            <input type="text" name="source_reference" id="source_reference" class="form-control @error('source_reference') is-invalid @enderror" value="{{ old('source_reference', $question->source_reference ?? '') }}">
            @error('source_reference')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mb-4">
    <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }} (Ctrl + S)</button>
</div>

@push('scripts')
<script>
$(function () {
    const select2Theme = 'bootstrap4';

    if ($.fn.select2) {
        $('#topic_id').select2({
            theme: select2Theme,
            width: '100%',
            allowClear: true,
            placeholder: $('#topic_id').data('placeholder') || 'Selecione o assunto',
            ajax: {
                url: '{{ route('admin.questions.ajax.topics') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        subject_id: $('#subject_id').val()
                    };
                },
                processResults: function (data) {
                    return data;
                }
            }
        });

        $('#exam_id').select2({
            theme: select2Theme,
            width: '100%',
            allowClear: true,
            placeholder: $('#exam_id').data('placeholder') || 'Selecione o concurso/prova',
            ajax: {
                url: '{{ route('admin.questions.ajax.exams') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        corporation_id: $('#corporation_id').val()
                    };
                },
                processResults: function (data) {
                    return data;
                }
            }
        });
    }

    $('#subject_id').on('change', function () {
        $('#topic_id').val(null).trigger('change');
    });

    $('#corporation_id').on('change', function () {
        $('#exam_id').val(null).trigger('change');
    });

    $(document).on('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            $('form').first().trigger('submit');
        }
    });
});
</script>
@include('admin.questions.snippets.source_material_select_js')
@endpush
