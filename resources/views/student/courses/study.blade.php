@extends('layouts.student')

@section('title', 'Estudar - ' . $course->title)

@push('styles')
<style>
    .course-study-card {
        position: relative;
    }

    .course-study-config {
        border: 1px solid var(--papirar-border);
        border-radius: 18px;
        background: #f8fafc;
        padding: 1rem;
        margin-bottom: 1.25rem;
    }

    .course-study-mobile-bar {
        display: none;
    }

    @media (max-width: 767.98px) {
        .course-study-card {
            padding: 1rem !important;
        }

        .course-study-config {
            padding: .9rem;
        }

        .course-study-desktop-submit {
            display: none !important;
        }

        .course-study-mobile-spacer {
            height: 92px;
        }

        .course-study-mobile-bar {
            display: block !important;
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 1055 !important;
            padding: .7rem .85rem calc(.7rem + env(safe-area-inset-bottom));
            background: rgba(255, 255, 255, .98);
            border-top: 1px solid var(--papirar-border);
            box-shadow: 0 -10px 30px rgba(15, 35, 68, .16);
        }

        .course-study-mobile-bar-inner {
            display: flex;
            align-items: center;
            gap: .75rem;
            max-width: 1220px;
            margin: 0 auto;
        }

        .course-study-mobile-meta {
            min-width: 0;
            flex: 1 1 auto;
        }

        .course-study-mobile-meta strong,
        .course-study-mobile-meta span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .course-study-mobile-meta strong {
            color: var(--papirar-navy);
            font-size: .86rem;
        }

        .course-study-mobile-meta span {
            color: var(--papirar-muted);
            font-size: .76rem;
        }

        .course-study-mobile-bar .btn {
            flex: 0 0 auto;
            min-width: 138px;
        }

        .accordion-button {
            padding: .9rem .85rem;
        }

        .accordion-button .badge {
            font-size: .72rem;
        }

        .accordion-body {
            padding: .85rem;
        }
    }
</style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Estudar: {{ $course->title }}</h1>
            <p class="page-subtitle">Monte uma sessão com várias disciplinas e tópicos específicos do curso.</p>
        </div>
        <a href="{{ route('student.courses.show', $course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 course-study-card">
                <form method="POST" action="{{ route('student.course-study.start', $course) }}" id="course-study-form">
                    @csrf

                    {{-- Configurações principais ficam antes da lista longa de disciplinas/tópicos --}}
                    <div class="course-study-config">
                        <div class="section-title mb-3">Configuração da sessão</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fonte/Bibliografia</label>
                                <select name="source_material_id" class="form-control">
                                    <option value="">Todas as fontes</option>
                                    @foreach($sourceMaterials as $sourceMaterial)
                                        <option value="{{ $sourceMaterial->id }}" @selected(old('source_material_id') == $sourceMaterial->id)>
                                            {{ $sourceMaterial->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label">Dificuldade</label>
                                <select name="difficulty" class="form-control">
                                    <option value="">Todas</option>
                                    <option value="easy" @selected(old('difficulty') === 'easy')>Fácil</option>
                                    <option value="medium" @selected(old('difficulty') === 'medium')>Média</option>
                                    <option value="hard" @selected(old('difficulty') === 'hard')>Difícil</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label">Quantidade</label>
                                <input
                                    type="number"
                                    name="quantity"
                                    id="study-quantity"
                                    min="1"
                                    max="100"
                                    class="form-control"
                                    value="{{ old('quantity', 10) }}"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Modo</label>
                                <select name="mode" id="study-mode" class="form-control">
                                    <option value="train" @selected(old('mode', 'train') === 'train')>Treino</option>
                                    <option value="review" @selected(old('mode') === 'review')>Revisar questões erradas</option>
                                    <option value="favorites" @selected(old('mode') === 'favorites')>Estudar favoritas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <div class="section-title mb-1">Disciplinas e tópicos</div>
                            <div class="small-muted">
                                Selecione uma ou mais disciplinas. Abra a disciplina apenas se quiser restringir por tópicos.
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-content">
                                Selecionar tudo
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-content">
                                Limpar
                            </button>
                        </div>
                    </div>

                    <div class="accordion" id="studyScopeAccordion">
                        @forelse($subjects as $subject)
                            @php($subjectTopics = $topics->get($subject->id, collect()))

                            <div class="accordion-item border rounded-4 mb-2 overflow-hidden">
                                <h2 class="accordion-header" id="heading-subject-{{ $subject->id }}">
                                    <button
                                        class="accordion-button collapsed bg-white"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-subject-{{ $subject->id }}"
                                        aria-expanded="false"
                                        aria-controls="collapse-subject-{{ $subject->id }}"
                                    >
                                        <div class="form-check mb-0" onclick="event.stopPropagation();">
                                            <input
                                                class="form-check-input js-subject-check"
                                                type="checkbox"
                                                name="subject_ids[]"
                                                value="{{ $subject->id }}"
                                                id="subject-{{ $subject->id }}"
                                                data-subject-id="{{ $subject->id }}"
                                                @checked(in_array($subject->id, old('subject_ids', [])))
                                            >
                                            <label class="form-check-label fw-semibold" for="subject-{{ $subject->id }}">
                                                {{ $subject->name }}
                                            </label>
                                        </div>

                                        <span class="badge text-bg-light ms-2">
                                            {{ $subjectTopics->count() }} tópicos
                                        </span>
                                    </button>
                                </h2>

                                <div
                                    id="collapse-subject-{{ $subject->id }}"
                                    class="accordion-collapse collapse"
                                    data-bs-parent="#studyScopeAccordion"
                                >
                                    <div class="accordion-body bg-light">
                                        @if($subjectTopics->isEmpty())
                                            <div class="small-muted">
                                                Esta disciplina não possui tópicos vinculados ao curso.
                                                Marcando a disciplina, todas as questões da disciplina poderão entrar no treino.
                                            </div>
                                        @else
                                            <div class="row g-2">
                                                @foreach($subjectTopics as $topic)
                                                    <div class="col-12 col-md-6">
                                                        <div class="form-check bg-white border rounded-3 p-3 ps-5 h-100">
                                                            <input
                                                                class="form-check-input js-topic-check"
                                                                type="checkbox"
                                                                name="topic_ids[]"
                                                                value="{{ $topic->id }}"
                                                                id="topic-{{ $topic->id }}"
                                                                data-subject-id="{{ $subject->id }}"
                                                                @checked(in_array($topic->id, old('topic_ids', [])))
                                                            >
                                                            <label class="form-check-label" for="topic-{{ $topic->id }}">
                                                                {{ $topic->name }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning mb-0">
                                Nenhuma disciplina disponível para este curso.
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-end mt-4 course-study-desktop-submit">
                        <button class="btn btn-primary btn-lg px-4">Iniciar sessão</button>
                    </div>

                    <div class="course-study-mobile-spacer d-md-none" aria-hidden="true"></div>

                    {{-- Barra fixa exclusiva do celular --}}
                    <div class="course-study-mobile-bar" id="course-study-mobile-bar">
                        <div class="course-study-mobile-bar-inner">
                            <div class="course-study-mobile-meta">
                                <strong id="mobile-study-quantity">{{ old('quantity', 10) }} questões</strong>
                                <span id="mobile-study-mode">
                                    @switch(old('mode', 'train'))
                                        @case('review')
                                            Revisar questões erradas
                                            @break
                                        @case('favorites')
                                            Estudar favoritas
                                            @break
                                        @default
                                            Treino
                                    @endswitch
                                </span>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Iniciar sessão
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Como usar</div>
                <ul class="list-clean mb-0">
                    <li class="py-2">Marque a disciplina para estudar todo o conteúdo dela.</li>
                    <li class="py-2">Abra a disciplina e marque tópicos para restringir o treino.</li>
                    <li class="py-2">Você pode misturar disciplinas e tópicos diferentes na mesma sessão.</li>
                    <li class="py-2">Use “Estudar favoritas” para revisar questões marcadas.</li>
                </ul>
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Favoritas</div>
                <p class="small-muted">Questões marcadas com estrela ficam salvas para revisão posterior.</p>
                <a href="{{ route('student.courses.favorites.index', $course) }}" class="btn btn-outline-primary w-100">
                    Ver favoritas do curso
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subjectChecks = Array.from(document.querySelectorAll('.js-subject-check'));
    const topicChecks = Array.from(document.querySelectorAll('.js-topic-check'));
    const selectAllBtn = document.getElementById('select-all-content');
    const clearBtn = document.getElementById('clear-content');
    const quantityInput = document.getElementById('study-quantity');
    const modeSelect = document.getElementById('study-mode');
    const mobileQuantity = document.getElementById('mobile-study-quantity');
    const mobileMode = document.getElementById('mobile-study-mode');

    function syncSubject(subjectId) {
        const subject = subjectChecks.find(item => item.dataset.subjectId === String(subjectId));
        const topics = topicChecks.filter(item => item.dataset.subjectId === String(subjectId));

        if (!subject || topics.length === 0) {
            return;
        }

        const checkedCount = topics.filter(item => item.checked).length;
        subject.checked = checkedCount === topics.length;
        subject.indeterminate = checkedCount > 0 && checkedCount < topics.length;
    }

    function updateMobileSummary() {
        if (mobileQuantity && quantityInput) {
            const quantity = quantityInput.value || 0;
            mobileQuantity.textContent = `${quantity} ${Number(quantity) === 1 ? 'questão' : 'questões'}`;
        }

        if (mobileMode && modeSelect) {
            const selectedOption = modeSelect.options[modeSelect.selectedIndex];
            mobileMode.textContent = selectedOption ? selectedOption.textContent : '';
        }
    }

    subjectChecks.forEach(function (subject) {
        subject.addEventListener('change', function () {
            const topics = topicChecks.filter(item => item.dataset.subjectId === subject.dataset.subjectId);

            topics.forEach(item => item.checked = subject.checked);
            subject.indeterminate = false;
        });
    });

    topicChecks.forEach(function (topic) {
        topic.addEventListener('change', function () {
            syncSubject(topic.dataset.subjectId);
        });
    });

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            subjectChecks.forEach(item => {
                item.checked = true;
                item.indeterminate = false;
            });

            topicChecks.forEach(item => item.checked = true);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            subjectChecks.forEach(item => {
                item.checked = false;
                item.indeterminate = false;
            });

            topicChecks.forEach(item => item.checked = false);
        });
    }

    if (quantityInput) {
        quantityInput.addEventListener('input', updateMobileSummary);
    }

    if (modeSelect) {
        modeSelect.addEventListener('change', updateMobileSummary);
    }

    subjectChecks.forEach(item => syncSubject(item.dataset.subjectId));
    updateMobileSummary();
});
</script>
@endpush
