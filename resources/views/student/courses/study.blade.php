@extends('layouts.student')

@section('title', 'Estudar - ' . $course->title)

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
            <div class="card-soft p-4">
                <form method="POST" action="{{ route('student.course-study.start', $course) }}" id="course-study-form">
                    @csrf

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <div class="section-title mb-1">Disciplinas e tópicos</div>
                            <div class="small-muted">Selecione uma ou mais disciplinas. Abra a disciplina para escolher tópicos específicos.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-content">Selecionar tudo</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-content">Limpar</button>
                        </div>
                    </div>

                    <div class="accordion" id="studyScopeAccordion">
                        @forelse($subjects as $subject)
                            @php($subjectTopics = $topics->get($subject->id, collect()))
                            <div class="accordion-item border rounded-4 mb-2 overflow-hidden">
                                <h2 class="accordion-header" id="heading-subject-{{ $subject->id }}">
                                    <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-subject-{{ $subject->id }}">
                                        <div class="form-check mb-0" onclick="event.stopPropagation();">
                                            <input class="form-check-input js-subject-check" type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" id="subject-{{ $subject->id }}" data-subject-id="{{ $subject->id }}" @checked(in_array($subject->id, old('subject_ids', [])))>
                                            <label class="form-check-label fw-semibold" for="subject-{{ $subject->id }}">{{ $subject->name }}</label>
                                        </div>
                                        <span class="badge text-bg-light ms-2">{{ $subjectTopics->count() }} tópicos</span>
                                    </button>
                                </h2>
                                <div id="collapse-subject-{{ $subject->id }}" class="accordion-collapse collapse" data-bs-parent="#studyScopeAccordion">
                                    <div class="accordion-body bg-light">
                                        @if($subjectTopics->isEmpty())
                                            <div class="small-muted">Esta disciplina não possui tópicos vinculados ao curso. Marcando a disciplina, todas as questões da disciplina poderão entrar no treino.</div>
                                        @else
                                            <div class="row g-2">
                                                @foreach($subjectTopics as $topic)
                                                    <div class="col-md-6">
                                                        <div class="form-check bg-white border rounded-3 p-3 ps-5 h-100">
                                                            <input class="form-check-input js-topic-check" type="checkbox" name="topic_ids[]" value="{{ $topic->id }}" id="topic-{{ $topic->id }}" data-subject-id="{{ $subject->id }}" @checked(in_array($topic->id, old('topic_ids', [])))>
                                                            <label class="form-check-label" for="topic-{{ $topic->id }}">{{ $topic->name }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning mb-0">Nenhuma disciplina disponível para este curso.</div>
                        @endforelse
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Fonte/Bibliografia</label>
                            <select name="source_material_id" class="form-control">
                                <option value="">Todas as fontes</option>
                                @foreach($sourceMaterials as $sourceMaterial)
                                    <option value="{{ $sourceMaterial->id }}" @selected(old('source_material_id') == $sourceMaterial->id)>{{ $sourceMaterial->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Dificuldade</label>
                            <select name="difficulty" class="form-control">
                                <option value="">Todas</option>
                                <option value="easy" @selected(old('difficulty') === 'easy')>Fácil</option>
                                <option value="medium" @selected(old('difficulty') === 'medium')>Média</option>
                                <option value="hard" @selected(old('difficulty') === 'hard')>Difícil</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantity" min="1" max="100" class="form-control" value="{{ old('quantity', 10) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Modo</label>
                            <select name="mode" class="form-control">
                                <option value="train" @selected(old('mode', 'train') === 'train')>Treino</option>
                                <option value="review" @selected(old('mode') === 'review')>Revisar questões erradas</option>
                                <option value="favorites" @selected(old('mode') === 'favorites')>Estudar favoritas</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary">Iniciar sessão</button>
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
                <a href="{{ route('student.courses.favorites.index', $course) }}" class="btn btn-outline-primary w-100">Ver favoritas do curso</a>
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

    function syncSubject(subjectId) {
        const subject = subjectChecks.find(item => item.dataset.subjectId === String(subjectId));
        const topics = topicChecks.filter(item => item.dataset.subjectId === String(subjectId));
        if (!subject || topics.length === 0) return;

        const checkedCount = topics.filter(item => item.checked).length;
        subject.checked = checkedCount === topics.length;
        subject.indeterminate = checkedCount > 0 && checkedCount < topics.length;
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
            subjectChecks.forEach(item => { item.checked = true; item.indeterminate = false; });
            topicChecks.forEach(item => item.checked = true);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            subjectChecks.forEach(item => { item.checked = false; item.indeterminate = false; });
            topicChecks.forEach(item => item.checked = false);
        });
    }

    subjectChecks.forEach(item => syncSubject(item.dataset.subjectId));
});
</script>
@endpush
