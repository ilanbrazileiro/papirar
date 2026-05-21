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

<form action="{{ $formAction }}" method="POST" class="question-form" id="question-form">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <input type="hidden" name="question_type" value="{{ old('question_type', $question->question_type ?? 'multiple_choice') ?: 'multiple_choice' }}">

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-question-circle mr-1"></i> Enunciado da questão</h3>

            <button type="button" class="btn btn-outline-primary" id="papirar-question-preview-button">
                    <i class="fas fa-eye"></i> Pré-visualizar como aluno
                </button>

        </div>
        <div class="card-body">
            <div class="alert alert-info small mb-3">
                Para inserir imagem, clique no botão de imagem do editor. O arquivo será salvo no Storage e inserido no enunciado automaticamente.
            </div>
            
            

            <div class="form-group">
                <label for="statement">Enunciado <span class="text-danger">*</span></label>
                <textarea id="statement" name="statement" class="form-control papirar-rich-editor @error('statement') is-invalid @enderror" rows="8">{{ old('statement', $question->statement ?? '') }}</textarea>
                @error('statement')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list-ol mr-1"></i> Alternativas</h3>
        </div>
        <div class="card-body">
            @foreach($letters as $index => $letter)
                @php
                    $alt = $alts->firstWhere('letter', $letter);
                    $oldText = old("alternatives.$index.text", $alt->text ?? '');
                    $oldLetter = old("alternatives.$index.letter", $letter);
                @endphp

                <div class="border rounded p-3 mb-3 bg-light">
                    <input type="hidden" name="alternatives[{{ $index }}][letter]" value="{{ $oldLetter }}">
                    <div class="form-row align-items-start">
                        <div class="col-md-1">
                            <label class="d-block">Letra</label>
                            <span class="badge badge-dark p-2">{{ $letter }}</span>
                        </div>
                        <div class="col-md-9">
                            <label for="alternative_{{ $letter }}">Texto da alternativa {{ $letter }} <span class="text-danger">*</span></label>
                            <textarea id="alternative_{{ $letter }}" name="alternatives[{{ $index }}][text]" class="form-control @error("alternatives.$index.text") is-invalid @enderror" rows="2">{{ $oldText }}</textarea>
                            @error("alternatives.$index.text")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2 pt-md-4">
                            <div class="custom-control custom-radio mt-2">
                                <input type="radio" id="correct_{{ $letter }}" name="correct_letter" value="{{ $letter }}" class="custom-control-input" {{ $correctLetter === $letter ? 'checked' : '' }}>
                                <label class="custom-control-label" for="correct_{{ $letter }}">Correta</label>
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

    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-comments mr-1"></i> Comentário / gabarito comentado</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="commented_answer">Comentário</label>
                <textarea id="commented_answer" name="commented_answer" class="form-control papirar-rich-editor @error('commented_answer') is-invalid @enderror" rows="6">{{ old('commented_answer', $question->commented_answer ?? '') }}</textarea>
                @error('commented_answer')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card card-light card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Configurações</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="corporation_id">Corporação</label>
                    <select id="corporation_id" name="corporation_id" class="form-control @error('corporation_id') is-invalid @enderror">
                        <option value="">Questão geral / sem corporação específica</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" {{ (string) old('corporation_id', $question->corporation_id ?? '') === (string) $corporation->id ? 'selected' : '' }}>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('corporation_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="subject_id">Disciplina <span class="text-danger">*</span></label>
                    <select id="subject_id" name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                        <option value="">Selecione</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (string) old('subject_id', $question->subject_id ?? '') === (string) $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="topic_id">Assunto</label>
                    <select id="topic_id" name="topic_id" class="form-control @error('topic_id') is-invalid @enderror">
                        <option value="{{ old('topic_id', $question->topic_id ?? '') }}">
                            @if(old('topic_id', $question->topic_id ?? null) && isset($selectedTopic) && $selectedTopic)
                                {{ $selectedTopic->name }}
                            @else
                                Selecione uma disciplina primeiro
                            @endif
                        </option>
                    </select>
                    <small class="form-text text-muted">Busca dinâmica por disciplina.</small>
                    @error('topic_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="exam_id">Concurso / prova de origem</label>
                    <select id="exam_id" name="exam_id" class="form-control @error('exam_id') is-invalid @enderror">
                        <option value="{{ old('exam_id', $question->exam_id ?? '') }}">
                            @if(old('exam_id', $question->exam_id ?? null) && isset($selectedExam) && $selectedExam)
                                {{ $selectedExam->title }} ({{ $selectedExam->year }}) - {{ $selectedExam->exam_type }}
                            @else
                                Sem prova de origem
                            @endif
                        </option>
                    </select>
                    <small class="form-text text-muted">Use apenas quando a questão veio de prova oficial.</small>
                    @error('exam_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="difficulty">Dificuldade</label>
                    <select id="difficulty" name="difficulty" class="form-control">
                        <option value="easy" {{ old('difficulty', $question->difficulty ?? 'medium') === 'easy' ? 'selected' : '' }}>Fácil</option>
                        <option value="medium" {{ old('difficulty', $question->difficulty ?? 'medium') === 'medium' ? 'selected' : '' }}>Média</option>
                        <option value="hard" {{ old('difficulty', $question->difficulty ?? 'medium') === 'hard' ? 'selected' : '' }}>Difícil</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="source_type">Origem</label>
                    <select id="source_type" name="source_type" class="form-control">
                        <option value="official_exam" {{ old('source_type', $question->source_type ?? 'authored') === 'official_exam' ? 'selected' : '' }}>Prova oficial</option>
                        <option value="authored" {{ old('source_type', $question->source_type ?? 'authored') === 'authored' ? 'selected' : '' }}>Autoral</option>
                        <option value="adapted" {{ old('source_type', $question->source_type ?? 'authored') === 'adapted' ? 'selected' : '' }}>Adaptada</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="draft" {{ old('status', $question->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                        <option value="published" {{ old('status', $question->status ?? 'draft') === 'published' ? 'selected' : '' }}>Publicada</option>
                        <option value="archived" {{ old('status', $question->status ?? 'draft') === 'archived' ? 'selected' : '' }}>Arquivada</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="source_reference">Referência da fonte</label>
                <input type="text" id="source_reference" name="source_reference" class="form-control" value="{{ old('source_reference', $question->source_reference ?? '') }}" maxlength="255">
            </div>
        </div>
    </div>

    @include('admin.questions._preview_modal')

    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
        </a>
       <button type="submit" class="btn btn-primary">
            Salvar questão
            <small class="text-light ml-1">(Ctrl + S)</small>
        </button>
    </div>
</form>
@push('scripts')
<script>
    function submitQuestionFormByShortcut(event) {
        const isSaveShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's';

        if (!isSaveShortcut) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const form = document.getElementById('question-form');

        if (!form) {
            return;
        }

        if (window.tinymce) {
            window.tinymce.triggerSave();
        }

        form.requestSubmit();
    }

    document.addEventListener('keydown', submitQuestionFormByShortcut, true);

    document.addEventListener('tinymce-ready', function () {
        if (!window.tinymce) {
            return;
        }

        window.tinymce.editors.forEach(function (editor) {
            editor.on('keydown', function (event) {
                submitQuestionFormByShortcut(event);
            });
        });
    });

    window.addEventListener('load', function () {
        if (!window.tinymce) {
            return;
        }

        setTimeout(function () {
            window.tinymce.editors.forEach(function (editor) {
                editor.on('keydown', function (event) {
                    submitQuestionFormByShortcut(event);
                });
            });
        }, 800);
    });
</script>
@endpush
