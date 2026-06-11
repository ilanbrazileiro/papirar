@csrf

@php
    $checkedSubjects = collect(old('subject_ids', $selectedSubjects ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();

    $oldTopicIds = old('topic_ids');
    $checkedTopicsBySubject = is_array($oldTopicIds) ? $oldTopicIds : ($selectedTopicsBySubject ?? []);
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Revise os campos abaixo.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados do concurso</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="corporation_id" class="form-label">Corporação</label>
                <select name="corporation_id" id="corporation_id" class="form-control @error('corporation_id') is-invalid @enderror" required>
                    <option value="">Selecione...</option>
                    @foreach($corporations as $corporation)
                        <option value="{{ $corporation->id }}" @selected((int) old('corporation_id', $exam->corporation_id) === (int) $corporation->id)>
                            {{ $corporation->name }}
                        </option>
                    @endforeach
                </select>
                @error('corporation_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="title" class="form-label">Nome do concurso</label>
                <input type="text" name="title" id="title" value="{{ old('title', $exam->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="year" class="form-label">Ano</label>
                <input type="number" name="year" id="year" value="{{ old('year', $exam->year ?? now()->year) }}" class="form-control @error('year') is-invalid @enderror" min="2000" max="2100" required>
                @error('year')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="planned" @selected(old('status', $exam->status ?? 'planned') === 'planned')>Previsto</option>
                    <option value="published" @selected(old('status', $exam->status ?? 'planned') === 'published')>Publicado</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="exam_type" class="form-label">Tipo do concurso</label>
                <input type="text" name="exam_type" id="exam_type" value="{{ old('exam_type', $exam->exam_type) }}" class="form-control @error('exam_type') is-invalid @enderror" placeholder="Ex.: CHOE, CHOAE, CFS, CFC" required>
                <small class="text-muted">Use um tipo simples e consistente.</small>
                @error('exam_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <div class="form-check mt-2">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" id="active" value="1" class="form-check-input" @checked((bool) old('active', $exam->active ?? true))>
                    <label for="active" class="form-check-label">Ativo para aparecer ao aluno</label>
                </div>
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Descrição / observações</label>
                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $exam->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Disciplinas e tópicos cobrados</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Marque as disciplinas do concurso. Ao marcar uma disciplina pela primeira vez, todos os tópicos ativos dela serão selecionados automaticamente. Depois, desmarque apenas os tópicos que não caem no concurso.
        </p>

        @foreach($subjects as $subject)
            @php
                $subjectId = (int) $subject->id;
                $isSubjectChecked = in_array($subjectId, $checkedSubjects, true);
                $subjectTopics = $topicsBySubject[$subjectId] ?? collect();
                $checkedTopicIds = collect($checkedTopicsBySubject[$subjectId] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->all();
            @endphp

            <div class="border rounded p-3 mb-3 exam-subject-block" data-subject-id="{{ $subjectId }}">
                <div class="form-check mb-2">
                    <input
                        type="checkbox"
                        name="subject_ids[]"
                        id="subject_{{ $subjectId }}"
                        value="{{ $subjectId }}"
                        class="form-check-input exam-subject-checkbox"
                        @checked($isSubjectChecked)
                    >
                    <label for="subject_{{ $subjectId }}" class="form-check-label fw-bold">
                        {{ $subject->name }}
                    </label>

                    @if(($subject->scope ?? 'general') === 'corporation_specific')
                        <span class="badge bg-warning text-dark ms-2">específica</span>
                    @else
                        <span class="badge bg-secondary ms-2">geral</span>
                    @endif
                </div>

                <div class="exam-topic-area {{ $isSubjectChecked ? '' : 'd-none' }}" data-topic-area-for="{{ $subjectId }}">
                    @if($subjectTopics->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Esta disciplina ainda não possui tópicos cadastrados.
                        </div>
                    @else
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary exam-topic-select-all"
                                data-topic-action-subject-id="{{ $subjectId }}"
                            >
                                Selecionar todos os tópicos
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary exam-topic-clear-all"
                                data-topic-action-subject-id="{{ $subjectId }}"
                            >
                                Limpar tópicos
                            </button>
                        </div>

                        <div class="row">
                            @foreach($subjectTopics as $topic)
                                @php $topicId = (int) $topic->id; @endphp
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            name="topic_ids[{{ $subjectId }}][]"
                                            id="subject_{{ $subjectId }}_topic_{{ $topicId }}"
                                            value="{{ $topicId }}"
                                            class="form-check-input exam-topic-checkbox"
                                            data-topic-subject-id="{{ $subjectId }}"
                                            @checked(in_array($topicId, $checkedTopicIds, true))
                                            @disabled(!$isSubjectChecked)
                                        >
                                        <label for="subject_{{ $subjectId }}_topic_{{ $topicId }}" class="form-check-label">
                                            {{ $topic->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.planned-exams.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Salvar concurso</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function getTopicCheckboxes(block) {
            return Array.from(block.querySelectorAll('.exam-topic-checkbox'));
        }

        function setTopicsDisabled(block, disabled) {
            getTopicCheckboxes(block).forEach(function (topicCheckbox) {
                topicCheckbox.disabled = disabled;
            });
        }

        function checkAllTopics(block) {
            getTopicCheckboxes(block).forEach(function (topicCheckbox) {
                topicCheckbox.disabled = false;
                topicCheckbox.checked = true;
            });
        }

        function clearAllTopics(block) {
            getTopicCheckboxes(block).forEach(function (topicCheckbox) {
                topicCheckbox.checked = false;
            });
        }

        document.querySelectorAll('.exam-subject-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const block = checkbox.closest('.exam-subject-block');
                const area = block.querySelector('.exam-topic-area');
                const topicCheckboxes = getTopicCheckboxes(block);

                if (checkbox.checked) {
                    area.classList.remove('d-none');
                    setTopicsDisabled(block, false);

                    const hasAnyCheckedTopic = topicCheckboxes.some(function (topicCheckbox) {
                        return topicCheckbox.checked;
                    });

                    if (!hasAnyCheckedTopic) {
                        checkAllTopics(block);
                    }
                } else {
                    area.classList.add('d-none');
                    clearAllTopics(block);
                    setTopicsDisabled(block, true);
                }
            });
        });

        document.querySelectorAll('.exam-topic-select-all').forEach(function (button) {
            button.addEventListener('click', function () {
                const block = button.closest('.exam-subject-block');
                const subjectCheckbox = block.querySelector('.exam-subject-checkbox');
                const area = block.querySelector('.exam-topic-area');

                subjectCheckbox.checked = true;
                area.classList.remove('d-none');
                checkAllTopics(block);
            });
        });

        document.querySelectorAll('.exam-topic-clear-all').forEach(function (button) {
            button.addEventListener('click', function () {
                const block = button.closest('.exam-subject-block');
                clearAllTopics(block);
            });
        });
    });
</script>
