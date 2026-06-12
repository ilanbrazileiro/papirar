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

<form action="{{ $formAction }}" method="POST" id="question-form">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Enunciado da questão</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Para inserir imagem, clique no botão de imagem do editor. O arquivo será salvo no Storage e inserido no enunciado automaticamente.
            </p>

            <div class="mb-3">
                <label for="statement" class="form-label">Enunciado <span class="text-danger">*</span></label>
                <textarea
                    name="statement"
                    id="statement"
                    rows="8"
                    class="form-control papirar-rich-editor @error('statement') is-invalid @enderror"
                >{{ old('statement', $question->statement ?? '') }}</textarea>
                @error('statement')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            @include('admin.questions.snippets.duplicate_checker')
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
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Alternativa {{ $letter }}</strong>
                        <div class="form-check">
                            <input
                                type="radio"
                                name="correct_letter"
                                id="correct_letter_{{ $letter }}"
                                value="{{ $letter }}"
                                class="form-check-input"
                                @checked($correctLetter === $letter)
                            >
                            <label for="correct_letter_{{ $letter }}" class="form-check-label">Correta</label>
                        </div>
                    </div>

                    <input type="hidden" name="alternatives[{{ $index }}][letter]" value="{{ $oldLetter }}">

                    <label for="alternative_{{ strtolower($letter) }}" class="form-label">Texto da alternativa {{ $letter }} <span class="text-danger">*</span></label>
                    <textarea
                        name="alternatives[{{ $index }}][text]"
                        id="alternative_{{ strtolower($letter) }}"
                        rows="3"
                        class="form-control papirar-rich-editor @error("alternatives.$index.text") is-invalid @enderror"
                    >{{ $oldText }}</textarea>
                    @error("alternatives.$index.text")
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach

            @error('correct_letter')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Comentário / gabarito comentado</h3>
        </div>
        <div class="card-body">
            <label for="commented_answer" class="form-label">Comentário</label>
            <textarea
                name="commented_answer"
                id="commented_answer"
                rows="6"
                class="form-control papirar-rich-editor @error('commented_answer') is-invalid @enderror"
            >{{ old('commented_answer', $question->commented_answer ?? '') }}</textarea>
            @error('commented_answer')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
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
                    <select name="corporation_id" id="corporation_id" class="form-select @error('corporation_id') is-invalid @enderror">
                        <option value="">Questão geral / sem corporação específica</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((string) old('corporation_id', $question->corporation_id ?? '') === (string) $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('corporation_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="subject_id" class="form-label">Disciplina <span class="text-danger">*</span></label>
                    <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror">
                        <option value="">Selecione</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) old('subject_id', $question->subject_id ?? '') === (string) $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="topic_id" class="form-label">Assunto</label>
                    <select name="topic_id" id="topic_id" class="form-select @error('topic_id') is-invalid @enderror">
                        <option value="">Selecione uma disciplina primeiro</option>
                        @if(old('topic_id', $question->topic_id ?? null) && isset($selectedTopic) && $selectedTopic)
                            <option value="{{ $selectedTopic->id }}" selected>{{ $selectedTopic->name }}</option>
                        @endif
                    </select>
                    <div class="form-text">Busca dinâmica por disciplina.</div>
                    @error('topic_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="exam_id" class="form-label">Concurso / prova de origem</label>
                    <select name="exam_id" id="exam_id" class="form-select @error('exam_id') is-invalid @enderror">
                        <option value="">Sem prova de origem</option>
                        @if(old('exam_id', $question->exam_id ?? null) && isset($selectedExam) && $selectedExam)
                            <option value="{{ $selectedExam->id }}" selected>
                                {{ $selectedExam->title }} ({{ $selectedExam->year }}) - {{ $selectedExam->exam_type }}
                            </option>
                        @endif
                    </select>
                    <div class="form-text">Use apenas quando a questão veio de prova oficial.</div>
                    @error('exam_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @include('admin.questions.snippets._source_material_select')

            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <label for="difficulty" class="form-label">Dificuldade</label>
                    <select name="difficulty" id="difficulty" class="form-select @error('difficulty') is-invalid @enderror">
                        <option value="easy" @selected(old('difficulty', $question->difficulty ?? 'medium') === 'easy')>Fácil</option>
                        <option value="medium" @selected(old('difficulty', $question->difficulty ?? 'medium') === 'medium')>Média</option>
                        <option value="hard" @selected(old('difficulty', $question->difficulty ?? 'medium') === 'hard')>Difícil</option>
                    </select>
                    @error('difficulty')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="source_type" class="form-label">Origem</label>
                    <select name="source_type" id="source_type" class="form-select @error('source_type') is-invalid @enderror">
                        <option value="exam" @selected(old('source_type', $question->source_type ?? 'authored') === 'exam')>Prova oficial</option>
                        <option value="authored" @selected(old('source_type', $question->source_type ?? 'authored') === 'authored')>Autoral</option>
                        <option value="adapted" @selected(old('source_type', $question->source_type ?? 'authored') === 'adapted')>Adaptada</option>
                    </select>
                    @error('source_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="draft" @selected(old('status', $question->status ?? 'draft') === 'draft')>Rascunho</option>
                        <option value="published" @selected(old('status', $question->status ?? 'draft') === 'published')>Publicada</option>
                        <option value="archived" @selected(old('status', $question->status ?? 'draft') === 'archived')>Arquivada</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="source_reference" class="form-label">Referência da fonte</label>
                <input
                    type="text"
                    name="source_reference"
                    id="source_reference"
                    value="{{ old('source_reference', $question->source_reference ?? '') }}"
                    class="form-control @error('source_reference') is-invalid @enderror"
                    maxlength="255"
                >
                @error('source_reference')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }} <span class="text-white-50">(Ctrl + S)</span></button>
    </div>
</form>

@push('scripts')
    @include('admin.questions.snippets.source_material_select_js')
    @include('admin.questions.snippets.duplicate_checker_js')

    <script>
        (function () {
            if (window.jQuery && $.fn.select2) {
                $('#exam_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Sem prova de origem',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('admin.questions.ajax.exams') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                corporation_id: $('#corporation_id').val()
                            };
                        }
                    }
                });

                $('#topic_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Selecione uma disciplina primeiro',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('admin.questions.ajax.topics') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                subject_id: $('#subject_id').val()
                            };
                        }
                    }
                });
            }

            const subjectSelect = document.getElementById('subject_id');
            if (subjectSelect) {
                subjectSelect.addEventListener('change', function () {
                    const topic = document.getElementById('topic_id');
                    if (topic && window.jQuery && $.fn.select2) {
                        $('#topic_id').val(null).trigger('change');
                    }
                });
            }
        })();
    </script>
@endpush
