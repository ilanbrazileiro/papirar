@extends('layouts.student')

@section('title', 'Estudar - ' . $course->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Estudar: {{ $course->title }}</h1>
            <p class="page-subtitle">Configure uma sessão usando apenas as questões disponíveis neste curso.</p>
        </div>
        <a href="{{ route('student.courses.show', $course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
    </div>

    <div class="card-soft p-4">
        <form method="POST" action="{{ route('student.course-study.start', $course) }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Disciplina</label>
                    <select name="subject_id" id="subject_id" class="form-control">
                        <option value="">Todas as disciplinas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tópico</label>
                    <select name="topic_id" id="topic_id" class="form-control">
                        <option value="">Todos os tópicos</option>
                        @foreach($topics as $subjectId => $subjectTopics)
                            @foreach($subjectTopics as $topic)
                                <option value="{{ $topic->id }}" data-subject-id="{{ $topic->subject_id }}" @selected(old('topic_id') == $topic->id)>
                                    {{ $topic->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

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
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-primary">Iniciar sessão</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subjectSelect = document.getElementById('subject_id');
    const topicSelect = document.getElementById('topic_id');

    if (!subjectSelect || !topicSelect) return;

    const allTopicOptions = Array.from(topicSelect.querySelectorAll('option'));

    function filterTopics() {
        const selectedSubject = subjectSelect.value;

        allTopicOptions.forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = selectedSubject && option.dataset.subjectId !== selectedSubject;
        });

        const selectedOption = topicSelect.options[topicSelect.selectedIndex];
        if (selectedOption && selectedOption.hidden) {
            topicSelect.value = '';
        }
    }

    subjectSelect.addEventListener('change', filterTopics);
    filterTopics();
});
</script>
@endpush
