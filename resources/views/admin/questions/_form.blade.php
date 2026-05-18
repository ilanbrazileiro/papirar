@php
    $letters = ['A', 'B', 'C', 'D', 'E'];
    $alts = $question->alternatives?->sortBy('letter')->values() ?? collect();
    $selectedCorporation = old('corporation_id', $question->corporation_id);
    $selectedSubject = old('subject_id', $question->subject_id);
    $selectedTopicId = old('topic_id', $question->topic_id);
    $selectedExamId = old('exam_id', $question->exam_id);
    $correctLetter = old('correct_letter', optional($question->alternatives?->firstWhere('is_correct', true))->letter ?? 'A');
@endphp

<form method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}" class="papirar-admin-form">
    @csrf
    @if($question->exists)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="corporation_id">Corporação</label>
                <select name="corporation_id" id="corporation_id" class="form-control">
                    <option value="">Questão geral / sem corporação específica</option>
                    @foreach($corporations as $corporation)
                        <option value="{{ $corporation->id }}" @selected((string) $selectedCorporation === (string) $corporation->id)>
                            {{ $corporation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="subject_id">Disciplina</label>
                <select name="subject_id" id="subject_id" class="form-control" required>
                    <option value="">Selecione</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) $selectedSubject === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="topic_id">Assunto</label>
                <select name="topic_id" id="topic_id" class="form-control" data-selected-id="{{ $selectedTopicId }}" data-selected-text="{{ $selectedTopic->name ?? '' }}">
                    <option value="">Selecione a disciplina primeiro</option>
                    @if($selectedTopicId && $selectedTopic)
                        <option value="{{ $selectedTopicId }}" selected>{{ $selectedTopic->name }}</option>
                    @endif
                </select>
                <small class="form-text text-muted">Busca dinâmica por disciplina.</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="exam_id">Concurso / prova de origem</label>
                <select name="exam_id" id="exam_id" class="form-control" data-selected-id="{{ $selectedExamId }}" data-selected-text="{{ $selectedExam ? ($selectedExam->title . ' (' . $selectedExam->year . ') - ' . $selectedExam->exam_type) : '' }}">
                    <option value="">Sem prova de origem</option>
                    @if($selectedExamId && $selectedExam)
                        <option value="{{ $selectedExamId }}" selected>{{ $selectedExam->title }} ({{ $selectedExam->year }}) - {{ $selectedExam->exam_type }}</option>
                    @endif
                </select>
                <small class="form-text text-muted">Busca dinâmica por corporação.</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="difficulty">Dificuldade</label>
                <select name="difficulty" id="difficulty" class="form-control" required>
                    <option value="easy" @selected(old('difficulty', $question->difficulty) === 'easy')>Fácil</option>
                    <option value="medium" @selected(old('difficulty', $question->difficulty) === 'medium')>Média</option>
                    <option value="hard" @selected(old('difficulty', $question->difficulty) === 'hard')>Difícil</option>
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="source_type">Origem</label>
                <select name="source_type" id="source_type" class="form-control" required>
                    <option value="exam" @selected(old('source_type', $question->source_type) === 'exam')>Prova oficial</option>
                    <option value="authored" @selected(old('source_type', $question->source_type) === 'authored')>Autoral</option>
                    <option value="adapted" @selected(old('source_type', $question->source_type) === 'adapted')>Adaptada</option>
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="draft" @selected(old('status', $question->status) === 'draft')>Rascunho</option>
                    <option value="published" @selected(old('status', $question->status) === 'published')>Publicada</option>
                    <option value="archived" @selected(old('status', $question->status) === 'archived')>Arquivada</option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="source_reference">Referência da fonte</label>
        <input type="text" name="source_reference" id="source_reference" class="form-control" value="{{ old('source_reference', $question->source_reference) }}" maxlength="255">
    </div>

    <div class="form-group">
        <label for="statement">Enunciado</label>
        <textarea name="statement" id="statement" rows="10" class="form-control rich-editor" required>{{ old('statement', $question->statement) }}</textarea>
        <small class="form-text text-muted">Use o botão de imagem do editor para enviar figuras, mapas, gráficos ou charges da questão.</small>
    </div>

    <hr>
    <h4>Alternativas</h4>

    @foreach($letters as $index => $letter)
        @php
            $alt = $alts->firstWhere('letter', $letter);
            $oldText = old("alternatives.$index.text", $alt->text ?? '');
        @endphp
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-start">
                    <div class="col-md-2">
                        <div class="form-group mb-md-0">
                            <label>Letra</label>
                            <input type="text" name="alternatives[{{ $index }}][letter]" value="{{ $letter }}" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-md-0">
                            <label for="alternative_{{ $letter }}">Texto da alternativa {{ $letter }}</label>
                            <textarea name="alternatives[{{ $index }}][text]" id="alternative_{{ $letter }}" rows="3" class="form-control" required>{{ $oldText }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>Correta?</label>
                        <div class="form-check mt-2">
                            <input type="radio" name="correct_letter" id="correct_{{ $letter }}" class="form-check-input" value="{{ $letter }}" @checked($correctLetter === $letter)>
                            <label class="form-check-label" for="correct_{{ $letter }}">Marcar {{ $letter }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="form-group">
        <label for="commented_answer">Comentário / gabarito comentado</label>
        <textarea name="commented_answer" id="commented_answer" rows="8" class="form-control rich-editor">{{ old('commented_answer', $question->commented_answer) }}</textarea>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const corporationSelect = document.getElementById('corporation_id');
        const subjectSelect = document.getElementById('subject_id');
        const examSelect = document.getElementById('exam_id');
        const topicSelect = document.getElementById('topic_id');

        function resetSelect(select, placeholder) {
            select.innerHTML = '';
            const option = document.createElement('option');
            option.value = '';
            option.textContent = placeholder;
            select.appendChild(option);
        }

        function appendOptions(select, items, selectedId = '') {
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.text;
                if (String(item.id) === String(selectedId)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        async function loadTopics(selectedId = '') {
            const subjectId = subjectSelect.value;
            resetSelect(topicSelect, subjectId ? 'Carregando...' : 'Selecione a disciplina primeiro');
            if (!subjectId) return;

            const url = new URL("{{ route('admin.questions.ajax.topics') }}", window.location.origin);
            url.searchParams.set('subject_id', subjectId);

            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            resetSelect(topicSelect, 'Sem assunto específico');
            appendOptions(topicSelect, data.results || [], selectedId);
        }

        async function loadExams(selectedId = '') {
            const corporationId = corporationSelect.value;
            resetSelect(examSelect, corporationId ? 'Carregando...' : 'Sem prova de origem');

            const url = new URL("{{ route('admin.questions.ajax.exams') }}", window.location.origin);
            if (corporationId) url.searchParams.set('corporation_id', corporationId);

            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            resetSelect(examSelect, 'Sem prova de origem');
            appendOptions(examSelect, data.results || [], selectedId);
        }

        subjectSelect?.addEventListener('change', () => loadTopics());
        corporationSelect?.addEventListener('change', () => loadExams());

        if (subjectSelect?.value) loadTopics(topicSelect.dataset.selectedId || '');
        if (corporationSelect) loadExams(examSelect.dataset.selectedId || '');
    });
</script>
@endpush
