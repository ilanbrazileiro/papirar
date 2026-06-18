@extends('layouts.student')

@section('title', 'Questão')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Questão {{ $currentPosition }} de {{ $totalQuestions }}</h1>
            <p class="page-subtitle">
                {{ $session->course->title ?? 'Sessão de estudo' }}
                @if($question->subject) · {{ $question->subject->name }} @endif
                @if($question->topic) · {{ $question->topic->name }} @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('student.courses.questions.favorite', [$session->course_id, $question]) }}">
                @csrf
                <button class="btn {{ ($isFavorited ?? false) ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ ($isFavorited ?? false) ? '★ Favoritada' : '☆ Favoritar' }}
                </button>
            </form>
            <a href="{{ $session->course ? route('student.courses.show', $session->course) : route('student.courses.index') }}" class="btn btn-outline-primary">Voltar ao curso</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    @if($favorite ?? false)
        <div class="card-soft p-4 mb-4 border border-warning-subtle" id="favorite-note-card">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <div class="section-title mb-1">Anotação da favorita</div>
                    <p class="small-muted mb-0">
                        Registre por que esta questão merece atenção. A anotação ficará salva na sua lista de favoritas.
                    </p>
                </div>
                <a href="{{ route('student.courses.favorites.index', $session->course_id) }}" class="btn btn-sm btn-outline-secondary align-self-start">
                    Ver favoritas
                </a>
            </div>

            <form method="POST" action="{{ route('student.courses.favorites.note', [$session->course_id, $question]) }}" class="mt-3">
                @csrf
                @method('PATCH')

                <textarea name="note" rows="3" class="form-control" maxlength="3000" placeholder="Ex.: Errei porque confundi o conceito; revisar antes da prova; questão boa para fixar este tópico.">{{ old('note', $favorite->note) }}</textarea>

                @error('note')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-sm-center mt-3">
                    <span class="small-muted">Você pode salvar a anotação e continuar o estudo normalmente.</span>
                    <button class="btn btn-outline-primary">Salvar anotação</button>
                </div>
            </form>
        </div>
    @endif

    <div class="question-card mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @if($question->difficulty)
                <span class="meta-badge">Dificuldade: {{ $question->difficulty }}</span>
            @endif
            @if($question->sourceMaterial)
                <span class="meta-badge">Fonte: {{ $question->sourceMaterial->title }}</span>
            @endif
            @if($question->activeVideoLesson)
                <span class="meta-badge">Aula em vídeo disponível após responder</span>
            @endif
            @if($isFavorited ?? false)
                <span class="meta-badge">Favoritada</span>
            @endif
        </div>

        <div class="mb-4 papirar-katex">
            {!! $question->statement !!}
        </div>

        @if($userAnswer)
            @foreach($question->alternatives->sortBy('letter') as $alternative)
                @php
                    $isSelected = (int) $userAnswer->selected_alternative_id === (int) $alternative->id;
                    $class = $alternative->is_correct ? 'correct' : ($isSelected ? 'wrong' : '');
                @endphp
                <div class="alt-card {{ $class }} mb-2">
                    <strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}
                </div>
            @endforeach

            <div class="card-soft p-4 mt-4">
                <div class="section-title">Comentário</div>
                <div class="papirar-katex">{!! $question->commented_answer ?: 'Comentário ainda não cadastrado.' !!}</div>
            </div>

            @include('student.courses.partials.video-lesson', ['question' => $question])

            <form method="POST" action="{{ route('student.course-study.next', $session) }}" class="mt-4 text-end">
                @csrf
                <button class="btn btn-primary">Próxima questão</button>
            </form>
        @else
            <form method="POST" action="{{ route('student.course-study.answer', $session) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">

                <div class="small-muted mb-3">Use a tesoura para riscar alternativas que você quer eliminar antes de responder.</div>

                @foreach($question->alternatives->sortBy('letter') as $alternative)
                    <div class="alt-card d-flex align-items-start gap-2 mb-2 js-alternative-row" data-alt-id="{{ $alternative->id }}">
                        <button type="button" class="btn btn-sm btn-light border js-cut-alternative" title="Riscar alternativa" aria-label="Riscar alternativa">✂</button>
                        <label class="d-flex align-items-start gap-2 mb-0 flex-grow-1">
                            <input type="radio" name="selected_alternative_id" value="{{ $alternative->id }}" required class="mt-1">
                            <span><strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}</span>
                        </label>
                    </div>
                @endforeach

                <div class="text-end mt-4">
                    <button class="btn btn-primary">Responder</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .alternative-cut {
        opacity: .55;
        background: #f8f9fa;
    }

    .alternative-cut label span {
        text-decoration: line-through;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'papirar-cut-question-{{ $question->id }}';
    const rows = Array.from(document.querySelectorAll('.js-alternative-row'));

    let cutIds = [];
    try {
        cutIds = JSON.parse(localStorage.getItem(storageKey) || '[]');
    } catch (e) {
        cutIds = [];
    }

    function persist() {
        localStorage.setItem(storageKey, JSON.stringify(cutIds));
    }

    rows.forEach(function (row) {
        const altId = row.dataset.altId;
        if (cutIds.includes(altId)) {
            row.classList.add('alternative-cut');
        }

        const button = row.querySelector('.js-cut-alternative');
        if (!button) return;

        button.addEventListener('click', function () {
            row.classList.toggle('alternative-cut');

            if (row.classList.contains('alternative-cut')) {
                if (!cutIds.includes(altId)) cutIds.push(altId);
            } else {
                cutIds = cutIds.filter(id => id !== altId);
            }

            persist();
        });
    });
});
</script>
@endpush
