@extends('layouts.student')

@section('title', 'Estudar por concurso')

@push('styles')
<style>
    .study-subject-card {
        border: 1px solid var(--papirar-border);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .study-subject-card + .study-subject-card {
        margin-top: .75rem;
    }

    .study-subject-header {
        padding: .9rem 1rem;
    }

    .study-subject-summary {
        color: var(--papirar-muted);
        font-size: .84rem;
    }

    .study-topic-block {
        border-top: 1px solid var(--papirar-border);
        padding: .9rem 1rem 1rem;
        background: #fbfcfe;
    }

    .study-topic-toggle {
        white-space: nowrap;
    }

    .study-mobile-action {
        display: none;
    }

    @media (max-width: 767.98px) {
        .study-card {
            padding: 1rem !important;
        }

        .study-subject-header {
            padding: .85rem;
        }

        .study-topic-block {
            padding: .85rem;
        }

        .study-topic-actions {
            width: 100%;
        }

        .study-topic-actions .btn {
            flex: 1 1 auto;
        }

        .study-mobile-space {
            height: 92px;
        }

        .study-mobile-action {
            display: block;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1025;
            padding: .7rem .85rem calc(.7rem + env(safe-area-inset-bottom));
            background: rgba(255, 255, 255, .96);
            border-top: 1px solid var(--papirar-border);
            box-shadow: 0 -10px 30px rgba(15, 35, 68, .12);
            backdrop-filter: blur(10px);
        }

        .study-mobile-action-inner {
            max-width: 1220px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .study-mobile-action-meta {
            min-width: 0;
            flex: 1 1 auto;
        }

        .study-mobile-action-meta strong,
        .study-mobile-action-meta span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .study-mobile-action-meta strong {
            color: var(--papirar-navy);
            font-size: .86rem;
        }

        .study-mobile-action-meta span {
            color: var(--papirar-muted);
            font-size: .76rem;
        }

        .study-mobile-action .btn {
            flex: 0 0 auto;
            min-width: 145px;
        }
    }
</style>
@endpush

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

    <div class="card-soft p-4 p-md-5 study-card">
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

            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Quantidade</label>
                    <select name="quantity" id="quantity" class="form-control" required>
                        @foreach([10, 20, 30, 50, 100] as $quantity)
                            <option value="{{ $quantity }}" @selected(old('quantity', 20) == $quantity)>{{ $quantity }} questões</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modo</label>
                    <select name="mode" id="mode" class="form-control" required>
                        <option value="train" @selected(old('mode', 'train') === 'train')>Treino</option>
                        <option value="exam" @selected(old('mode') === 'exam')>Simulado rápido</option>
                        <option value="review" @selected(old('mode') === 'review')>Revisão de erros</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-end gap-3 mb-2">
                    <div>
                        <label class="form-label fw-semibold mb-0">Disciplinas e tópicos do concurso</label>
                        <div class="small text-muted mt-1">
                            Todas as disciplinas e tópicos vêm selecionados. Abra apenas o que quiser alterar.
                        </div>
                    </div>
                </div>

                <div id="subjectsBox" class="border rounded p-2 p-md-3 bg-light">
                    <div class="text-muted p-2">Selecione um concurso para carregar as disciplinas e tópicos.</div>
                </div>

                <div class="small text-muted mt-2">
                    Os tópicos exibidos são aqueles vinculados ao concurso no painel administrativo.
                </div>
            </div>

            <div class="alert alert-info mt-4 mb-0">
                <strong>Estudo direcionado:</strong>
                ao escolher um concurso, o Papirar carrega apenas as disciplinas e tópicos configurados para ele no admin.
                Você pode manter todos selecionados ou abrir uma disciplina para escolher assuntos específicos.
            </div>

            <div class="d-none d-md-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-4 js-start-button" disabled>
                    Começar estudo
                </button>
            </div>

            <div class="study-mobile-space d-md-none" aria-hidden="true"></div>

            <div class="study-mobile-action d-md-none">
                <div class="study-mobile-action-inner">
                    <div class="study-mobile-action-meta">
                        <strong id="mobileStudyQuantity">20 questões</strong>
                        <span id="mobileStudyMode">Treino</span>
                    </div>
                    <button type="submit" class="btn btn-primary js-start-button" disabled>
                        Começar estudo
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const corporationSelect = document.getElementById('corporation_id');
            const examSelect = document.getElementById('exam_id');
            const subjectsBox = document.getElementById('subjectsBox');
            const startButtons = Array.from(document.querySelectorAll('.js-start-button'));
            const quantitySelect = document.getElementById('quantity');
            const modeSelect = document.getElementById('mode');
            const mobileStudyQuantity = document.getElementById('mobileStudyQuantity');
            const mobileStudyMode = document.getElementById('mobileStudyMode');

            function setStartButtonsDisabled(disabled) {
                startButtons.forEach((button) => {
                    button.disabled = disabled;
                });
            }

            function resetSubjects(message = 'Selecione um concurso para carregar as disciplinas e tópicos.') {
                subjectsBox.innerHTML = `<div class="text-muted p-2">${message}</div>`;
                setStartButtonsDisabled(true);
            }

            function selectedTopicCountForSubject(subjectId) {
                return subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]:checked`).length;
            }

            function totalTopicCountForSubject(subjectId) {
                return subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`).length;
            }

            function updateSubjectSummary(subjectId) {
                const subjectCheckbox = subjectsBox.querySelector(`input[name="subject_ids[]"][value="${subjectId}"]`);
                const summary = subjectsBox.querySelector(`[data-subject-summary="${subjectId}"]`);

                if (!subjectCheckbox || !summary) {
                    return;
                }

                const totalTopics = totalTopicCountForSubject(subjectId);

                if (!subjectCheckbox.checked) {
                    summary.textContent = 'Disciplina desmarcada';
                    return;
                }

                if (totalTopics === 0) {
                    summary.textContent = 'Sem tópicos específicos';
                    return;
                }

                const selectedTopics = selectedTopicCountForSubject(subjectId);
                summary.textContent = `${selectedTopics} de ${totalTopics} tópicos selecionados`;
            }

            function updateAllSubjectSummaries() {
                subjectsBox.querySelectorAll('input[name="subject_ids[]"]').forEach((subjectCheckbox) => {
                    updateSubjectSummary(subjectCheckbox.value);
                });
            }

            function updateStartButton() {
                const checkedSubjects = Array.from(subjectsBox.querySelectorAll('input[name="subject_ids[]"]:checked'));

                if (checkedSubjects.length === 0) {
                    setStartButtonsDisabled(true);
                    return;
                }

                const hasSubjectWithoutTopic = checkedSubjects.some((subjectCheckbox) => {
                    const subjectId = subjectCheckbox.value;
                    const topics = subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`);
                    return topics.length > 0 && selectedTopicCountForSubject(subjectId) === 0;
                });

                setStartButtonsDisabled(hasSubjectWithoutTopic);
            }

            function updateMobileActionSummary() {
                const quantityOption = quantitySelect.options[quantitySelect.selectedIndex];
                const modeOption = modeSelect.options[modeSelect.selectedIndex];

                mobileStudyQuantity.textContent = quantityOption ? quantityOption.textContent : '';
                mobileStudyMode.textContent = modeOption ? modeOption.textContent : '';
            }

            function setTopicBlockOpen(subjectId, open) {
                const topicBlock = subjectsBox.querySelector(`[data-topic-block-subject-id="${subjectId}"]`);
                const toggleButton = subjectsBox.querySelector(`[data-toggle-topics="${subjectId}"]`);

                if (!topicBlock || !toggleButton) {
                    return;
                }

                topicBlock.classList.toggle('d-none', !open);
                toggleButton.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggleButton.textContent = open ? 'Ocultar tópicos' : 'Alterar tópicos';
            }

            function setSubjectTopicsState(subjectId, enabled, markAllWhenEnabling = true) {
                const topicCheckboxes = subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`);
                const toggleButton = subjectsBox.querySelector(`[data-toggle-topics="${subjectId}"]`);

                topicCheckboxes.forEach((topicCheckbox) => {
                    topicCheckbox.disabled = !enabled;

                    if (!enabled) {
                        topicCheckbox.checked = false;
                    } else if (markAllWhenEnabling) {
                        topicCheckbox.checked = true;
                    }
                });

                if (toggleButton) {
                    toggleButton.disabled = !enabled;
                }

                if (!enabled) {
                    setTopicBlockOpen(subjectId, false);
                }

                updateSubjectSummary(subjectId);
            }

            function bindSubjectEvents() {
                subjectsBox.querySelectorAll('input[name="subject_ids[]"]').forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        setSubjectTopicsState(checkbox.value, checkbox.checked, true);
                        updateStartButton();
                    });
                });

                subjectsBox.querySelectorAll('[data-toggle-topics]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const subjectId = button.getAttribute('data-toggle-topics');
                        const topicBlock = subjectsBox.querySelector(`[data-topic-block-subject-id="${subjectId}"]`);

                        if (!topicBlock) {
                            return;
                        }

                        setTopicBlockOpen(subjectId, topicBlock.classList.contains('d-none'));
                    });
                });

                subjectsBox.querySelectorAll('[data-select-all-topics]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const subjectId = button.getAttribute('data-select-all-topics');

                        subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`).forEach((topicCheckbox) => {
                            topicCheckbox.checked = true;
                        });

                        updateSubjectSummary(subjectId);
                        updateStartButton();
                    });
                });

                subjectsBox.querySelectorAll('[data-clear-topics]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const subjectId = button.getAttribute('data-clear-topics');

                        subjectsBox.querySelectorAll(`input[data-topic-subject-id="${subjectId}"]`).forEach((topicCheckbox) => {
                            topicCheckbox.checked = false;
                        });

                        updateSubjectSummary(subjectId);
                        updateStartButton();
                    });
                });

                subjectsBox.querySelectorAll('input[data-topic-subject-id]').forEach((topicCheckbox) => {
                    topicCheckbox.addEventListener('change', () => {
                        const subjectId = topicCheckbox.getAttribute('data-topic-subject-id');
                        updateSubjectSummary(subjectId);
                        updateStartButton();
                    });
                });
            }

            quantitySelect.addEventListener('change', updateMobileActionSummary);
            modeSelect.addEventListener('change', updateMobileActionSummary);
            updateMobileActionSummary();

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
                    wrapper.className = 'study-subject-card';

                    const topicsHtml = topics.length
                        ? `
                            <div class="study-topic-block d-none" data-topic-block-subject-id="${subject.id}">
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
                                    <div class="small fw-semibold text-muted">Escolha os tópicos</div>
                                    <div class="d-flex gap-2 study-topic-actions">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-select-all-topics="${subject.id}">
                                            Todos
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-topics="${subject.id}">
                                            Limpar
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    ${topics.map((topic) => `
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <label class="border rounded p-2 d-flex gap-2 align-items-start h-100 bg-white" style="cursor:pointer;">
                                                <input
                                                    type="checkbox"
                                                    name="topic_ids[${subject.id}][]"
                                                    value="${topic.id}"
                                                    checked
                                                    class="form-check-input mt-1"
                                                    data-topic-subject-id="${subject.id}"
                                                >
                                                <span class="small">${topic.name}</span>
                                            </label>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `
                        : '';

                    const toggleHtml = topics.length
                        ? `
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary study-topic-toggle"
                                data-toggle-topics="${subject.id}"
                                aria-expanded="false"
                            >
                                Alterar tópicos
                            </button>
                        `
                        : '';

                    wrapper.innerHTML = `
                        <div class="study-subject-header d-flex align-items-center justify-content-between gap-3">
                            <label class="d-flex gap-2 align-items-start mb-0 flex-grow-1" style="cursor:pointer;">
                                <input type="checkbox" name="subject_ids[]" value="${subject.id}" checked class="form-check-input mt-1">
                                <span class="min-w-0">
                                    <span class="fw-semibold d-block">${subject.name}</span>
                                    <span class="study-subject-summary d-block" data-subject-summary="${subject.id}">
                                        ${topics.length} de ${topics.length} tópicos selecionados
                                    </span>
                                </span>
                            </label>
                            ${toggleHtml}
                        </div>
                        ${topicsHtml}
                    `;

                    subjectsBox.appendChild(wrapper);
                });

                bindSubjectEvents();
                updateAllSubjectSummaries();
                updateStartButton();
            });
        });
    </script>
@endsection
