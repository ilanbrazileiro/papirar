@extends('layouts.student')

@section('title', 'Estudar por concurso')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Estudar por concurso</h1>
            <p class="page-subtitle">Escolha a corporação, o concurso, as disciplinas e os tópicos que deseja treinar.</p>
        </div>
        <a href="{{ route('student.study.index') }}" class="btn btn-outline-primary">Filtro livre</a>
    </div>

    @if(session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Não foi possível iniciar o estudo.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-soft p-4 p-md-5">
        <form method="POST" action="{{ route('student.exam-study.start') }}" id="examStudyForm">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Corporação</label>
                    <select name="corporation_id" id="corporation_id" class="form-control form-control-lg" required>
                        <option value="">Selecione...</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected(old('corporation_id') == $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Concurso</label>
                    <select name="exam_id" id="exam_id" class="form-control form-control-lg" required disabled>
                        <option value="">Selecione a corporação primeiro</option>
                    </select>
                    <div class="small-muted mt-1">Concursos previstos e publicados aparecem aqui.</div>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-semibold">Disciplinas e tópicos do concurso</label>
                <div id="subjectsBox" class="border rounded p-3 bg-light">
                    <div class="text-muted">Selecione um concurso para carregar as disciplinas e tópicos.</div>
                </div>
                <div class="small text-muted mt-2">
                    Os tópicos exibidos são aqueles vinculados ao concurso no painel administrativo.
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Quantidade</label>
                    <select name="quantity" class="form-control" required>
                        @foreach([10, 20, 30, 50, 100] as $quantity)
                            <option value="{{ $quantity }}" @selected(old('quantity', 20) == $quantity)>{{ $quantity }} questões</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modo</label>
                    <select name="mode" class="form-control" required>
                        <option value="train" @selected(old('mode', 'train') === 'train')>Treino</option>
                        <option value="exam" @selected(old('mode') === 'exam')>Simulado rápido</option>
                        <option value="review" @selected(old('mode') === 'review')>Revisão de erros</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-0">
                <strong>Estudo direcionado:</strong>
                ao escolher um concurso, o Papirar carrega apenas as disciplinas e tópicos configurados para ele no admin.
                Você pode estudar todos os tópicos ou selecionar apenas um assunto específico, como Progressão Aritmética.
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-4" id="startButton" disabled>Começar estudo</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const corporationSelect = document.getElementById('corporation_id');
            const examSelect = document.getElementById('exam_id');
            const subjectsBox = document.getElementById('subjectsBox');
            const startButton = document.getElementById('startButton');

            function resetSubjects(message = 'Selecione um concurso para carregar as disciplinas e tópicos.') {
                subjectsBox.innerHTML = `<div class="text-muted">${message}</div>`;
                startButton.disabled = true;
            }

            function selectedSubjectCount() {
                return subjectsBox.querySelectorAll('input[name="subject_ids[]"]:checked').length;
            }

            function selectedTopicCountForSubject(subjectId) {
                return subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]:checked`).length;
            }

            function updateStartButton() {
                const checkedSubjects = Array.from(subjectsBox.querySelectorAll('input[name="subject_ids[]"]:checked'));

                if (checkedSubjects.length === 0) {
                    startButton.disabled = true;
                    return;
                }

                const hasSubjectWithoutTopic = checkedSubjects.some((subjectCheckbox) => {
                    const subjectId = subjectCheckbox.value;
                    const topics = subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`);
                    return topics.length > 0 && selectedTopicCountForSubject(subjectId) === 0;
                });

                startButton.disabled = hasSubjectWithoutTopic;
            }

            function setSubjectTopicsState(subjectId, enabled, markAllWhenEnabling = true) {
                const topicCheckboxes = subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`);

                topicCheckboxes.forEach((topicCheckbox) => {
                    topicCheckbox.disabled = !enabled;

                    if (!enabled) {
                        topicCheckbox.checked = false;
                    } else if (markAllWhenEnabling) {
                        topicCheckbox.checked = true;
                    }
                });

                const topicBlock = document.querySelector(`[data-topic-block-subject-id="${subjectId}"]`);
                if (topicBlock) {
                    topicBlock.classList.toggle('d-none', !enabled);
                }
            }

            function bindSubjectEvents() {
                subjectsBox.querySelectorAll('input[name="subject_ids[]"]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        setSubjectTopicsState(checkbox.value, checkbox.checked, true);
                        updateStartButton();
                    });
                });

                subjectsBox.querySelectorAll('[data-select-all-topics]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const subjectId = button.getAttribute('data-select-all-topics');
                        subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`).forEach((topicCheckbox) => {
                            topicCheckbox.checked = true;
                        });
                        updateStartButton();
                    });
                });

                subjectsBox.querySelectorAll('[data-clear-topics]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const subjectId = button.getAttribute('data-clear-topics');
                        subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`).forEach((topicCheckbox) => {
                            topicCheckbox.checked = false;
                        });
                        updateStartButton();
                    });
                });

                subjectsBox.querySelectorAll('input[data-topic-subject-id]').forEach((topicCheckbox) => {
                    topicCheckbox.addEventListener('change', updateStartButton);
                });
            }

            corporationSelect.addEventListener('change', async function () {
                const corporationId = this.value;
                examSelect.innerHTML = '<option value="">Carregando...</option>';
                examSelect.disabled = true;
                resetSubjects('Selecione um concurso para carregar as disciplinas e tópicos.');

                if (!corporationId) {
                    examSelect.innerHTML = '<option value="">Selecione a corporação primeiro</option>';
                    return;
                }

                const url = `{{ url('/aluno/estudo-por-concurso/corporations') }}/${corporationId}/exams`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const exams = await response.json();

                examSelect.innerHTML = '<option value="">Selecione...</option>';

                if (!exams.length) {
                    examSelect.innerHTML = '<option value="">Nenhum concurso ativo encontrado</option>';
                    return;
                }

                exams.forEach((exam) => {
                    const option = document.createElement('option');
                    option.value = exam.id;
                    option.textContent = `${exam.title} - ${exam.year} (${exam.status_label})`;
                    examSelect.appendChild(option);
                });

                examSelect.disabled = false;
            });

            examSelect.addEventListener('change', async function () {
                const examId = this.value;
                resetSubjects('Carregando disciplinas e tópicos...');

                if (!examId) {
                    resetSubjects();
                    return;
                }

                const url = `{{ url('/aluno/estudo-por-concurso/exams') }}/${examId}/subjects`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const subjects = await response.json();

                if (!subjects.length) {
                    resetSubjects('Nenhuma disciplina vinculada a este concurso.');
                    return;
                }

                subjectsBox.innerHTML = '';

                subjects.forEach((subject) => {
                    const topics = Array.isArray(subject.topics) ? subject.topics : [];
                    const wrapper = document.createElement('div');
                    wrapper.className = 'border rounded bg-white p-3 mb-3';

                    const topicsHtml = topics.length
                        ? `
                            <div class="mt-3" data-topic-block-subject-id="${subject.id}">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                    <div class="small fw-semibold text-muted">Tópicos</div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-select-all-topics="${subject.id}">Selecionar todos</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-topics="${subject.id}">Limpar</button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    ${topics.map((topic) => `
                                        <div class="col-md-6 col-lg-4">
                                            <label class="border rounded p-2 d-flex gap-2 align-items-start h-100" style="cursor:pointer;">
                                                <input type="checkbox" name="topic_ids[${subject.id}][]" value="${topic.id}" checked class="form-check-input mt-1" data-topic-subject-id="${subject.id}">
                                                <span class="small">${topic.name}</span>
                                            </label>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `
                        : `<div class="small text-warning mt-2">Esta disciplina ainda não possui tópicos configurados para este concurso.</div>`;

                    wrapper.innerHTML = `
                        <label class="d-flex gap-2 align-items-start" style="cursor:pointer;">
                            <input type="checkbox" name="subject_ids[]" value="${subject.id}" checked class="form-check-input mt-1">
                            <span>
                                <span class="fw-semibold d-block">${subject.name}</span>
                                <span class="small text-muted">${subject.scope_label}</span>
                            </span>
                        </label>
                        ${topicsHtml}
                    `;

                    subjectsBox.appendChild(wrapper);
                });

                startButton.disabled = false;
                bindSubjectEvents();
                updateStartButton();
            });
        });
    </script>
@endsection
