@if($errors->any())
    <div class="alert alert-danger">
        <strong>Confira os campos abaixo:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $exam->exists ? route('admin.exams.update', $exam) : route('admin.exams.store') }}">
    @csrf
    @if($exam->exists)
        @method('PUT')
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>Dados do concurso</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="corporation_id" class="form-label">Corporação</label>
                    <select name="corporation_id" id="corporation_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((int) old('corporation_id', $exam->corporation_id ?? 0) === (int) $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Nome do concurso</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $exam->title ?? '') }}" required maxlength="180">
                </div>

                <div class="col-md-3">
                    <label for="year" class="form-label">Ano</label>
                    <input type="number" name="year" id="year" class="form-control" value="{{ old('year', $exam->year ?? now()->year) }}" required min="1900" max="2100">
                </div>

                <div class="col-md-3">
                    <label for="exam_type" class="form-label">Tipo</label>
                    <input type="text" name="exam_type" id="exam_type" class="form-control" value="{{ old('exam_type', $exam->exam_type ?? '') }}" placeholder="CHOAE, CFS, CFC..." required maxlength="50">
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="published" @selected(old('status', $exam->status ?? 'published') === 'published')>Publicado</option>
                        <option value="planned" @selected(old('status', $exam->status ?? 'published') === 'planned')>Previsto</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked((bool) old('active', $exam->active ?? true))>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Descrição / observações</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $exam->description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Disciplinas e tópicos cobrados</strong>
                <div class="text-muted small">Marque a disciplina e selecione apenas os tópicos que caem neste concurso.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-all-subjects">Expandir/recolher</button>
        </div>
        <div class="card-body p-0">
            <div class="accordion" id="exam-subjects-accordion">
                @foreach($subjects as $subject)
                    @php
                        $subjectId = (int) $subject->id;
                        $oldSubjectSelected = old("subjects.$subjectId.selected");
                        $isSubjectSelected = $oldSubjectSelected !== null
                            ? (bool) $oldSubjectSelected
                            : in_array($subjectId, $selectedSubjects ?? [], true);

                        $oldTopics = old("subjects.$subjectId.topics");
                        $selectedTopicIds = $oldTopics !== null
                            ? array_map('intval', (array) $oldTopics)
                            : array_map('intval', $selectedTopicsBySubject[$subjectId] ?? []);

                        $topics = $topicsBySubject[$subjectId] ?? collect();
                        $collapseId = 'subject-collapse-' . $subjectId;
                    @endphp

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="subject-heading-{{ $subjectId }}">
                            <button class="accordion-button {{ $isSubjectSelected ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isSubjectSelected ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                <span class="me-3">
                                    <input type="hidden" name="subjects[{{ $subjectId }}][selected]" value="0">
                                    <input class="form-check-input subject-checkbox" type="checkbox" id="subject-{{ $subjectId }}" name="subjects[{{ $subjectId }}][selected]" value="1" data-subject-id="{{ $subjectId }}" @checked($isSubjectSelected) onclick="event.stopPropagation();">
                                </span>
                                <span>
                                    {{ $subject->name }}
                                    <span class="badge bg-light text-dark ms-2">{{ $topics->count() }} tópico(s)</span>
                                </span>
                            </button>
                        </h2>

                        <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $isSubjectSelected ? 'show' : '' }}" aria-labelledby="subject-heading-{{ $subjectId }}" data-bs-parent="#exam-subjects-accordion">
                            <div class="accordion-body">
                                @if($topics->isEmpty())
                                    <div class="alert alert-warning mb-0">
                                        Esta disciplina ainda não possui tópicos cadastrados.
                                    </div>
                                @else
                                    <div class="d-flex gap-2 mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary select-all-topics" data-subject-id="{{ $subjectId }}">Marcar todos</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary clear-all-topics" data-subject-id="{{ $subjectId }}">Limpar</button>
                                    </div>

                                    <div class="row g-2 topic-group" data-subject-id="{{ $subjectId }}">
                                        @foreach($topics as $topic)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check border rounded p-2 h-100">
                                                    <input class="form-check-input ms-0 me-2 topic-checkbox topic-checkbox-{{ $subjectId }}" type="checkbox" id="topic-{{ $topic->id }}" name="subjects[{{ $subjectId }}][topics][]" value="{{ $topic->id }}" @checked(in_array((int) $topic->id, $selectedTopicIds, true))>
                                                    <label class="form-check-label" for="topic-{{ $topic->id }}">{{ $topic->name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar concurso</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.subject-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const subjectId = this.dataset.subjectId;
            const collapse = document.getElementById('subject-collapse-' + subjectId);
            if (this.checked && collapse && !collapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false });
                bsCollapse.show();
            }
        });
    });

    document.querySelectorAll('.topic-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const subjectId = this.closest('.topic-group')?.dataset.subjectId;
            if (!subjectId) return;
            const subjectCheckbox = document.getElementById('subject-' + subjectId);
            if (this.checked && subjectCheckbox) {
                subjectCheckbox.checked = true;
            }
        });
    });

    document.querySelectorAll('.select-all-topics').forEach(function (button) {
        button.addEventListener('click', function () {
            const subjectId = this.dataset.subjectId;
            const subjectCheckbox = document.getElementById('subject-' + subjectId);
            if (subjectCheckbox) subjectCheckbox.checked = true;
            document.querySelectorAll('.topic-checkbox-' + subjectId).forEach(function (checkbox) {
                checkbox.checked = true;
            });
        });
    });

    document.querySelectorAll('.clear-all-topics').forEach(function (button) {
        button.addEventListener('click', function () {
            const subjectId = this.dataset.subjectId;
            document.querySelectorAll('.topic-checkbox-' + subjectId).forEach(function (checkbox) {
                checkbox.checked = false;
            });
        });
    });

    const toggleAll = document.getElementById('toggle-all-subjects');
    if (toggleAll) {
        toggleAll.addEventListener('click', function () {
            const collapses = Array.from(document.querySelectorAll('#exam-subjects-accordion .accordion-collapse'));
            const hasClosed = collapses.some(function (item) { return !item.classList.contains('show'); });
            collapses.forEach(function (item) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(item, { toggle: false });
                hasClosed ? bsCollapse.show() : bsCollapse.hide();
            });
        });
    }
});
</script>
@endpush
