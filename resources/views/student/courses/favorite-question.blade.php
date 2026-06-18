@extends('layouts.student')

@section('title', 'Favorita #' . $question->id)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Questão favorita #{{ $question->id }}</h1>
            <p class="page-subtitle">
                {{ $course->title }}
                @if($question->subject) · {{ $question->subject->name }} @endif
                @if($question->topic) · {{ $question->topic->name }} @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('student.courses.favorites.retry', [$course, $question]) }}">
                @csrf
                <button class="btn btn-primary">Refazer questão</button>
            </form>
            <a href="{{ route('student.courses.favorites.index', $course) }}" class="btn btn-outline-primary">Voltar às favoritas</a>
        </div>
    </div>

    <div class="question-card mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @if($question->difficulty)
                <span class="meta-badge">Dificuldade: {{ $question->difficulty }}</span>
            @endif
            @if($question->sourceMaterial)
                <span class="meta-badge">Fonte: {{ $question->sourceMaterial->title }}</span>
            @endif
            @if($question->activeVideoLesson)
                <span class="meta-badge">Aula em vídeo disponível</span>
            @endif
            <span class="meta-badge">Favoritada</span>
        </div>

        @if($lastAnswer)
            <div class="alert {{ $lastAnswer->is_correct ? 'alert-success' : 'alert-warning' }}">
                Última tentativa: {{ $lastAnswer->is_correct ? 'correta' : 'incorreta' }}
                @if($lastAnswer->selectedAlternative)
                    · marcada: {{ $lastAnswer->selectedAlternative->letter }}
                @endif
                @if($lastAnswer->answered_at)
                    · {{ $lastAnswer->answered_at->format('d/m/Y H:i') }}
                @endif
            </div>
        @endif

        @if($answerStats && $answerStats->total)
            <div class="small-muted mb-3">
                Histórico nesta questão: {{ (int) $answerStats->correct }} acerto(s) em {{ (int) $answerStats->total }} tentativa(s).
            </div>
        @endif

        <div class="mb-4 papirar-katex">
            {!! $question->statement !!}
        </div>

        @foreach($question->alternatives->sortBy('letter') as $alternative)
            <div class="alt-card {{ $alternative->is_correct ? 'correct' : '' }} mb-2">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}
                    </div>
                    @if($alternative->is_correct)
                        <span class="badge bg-success align-self-start">Gabarito</span>
                    @endif
                </div>
            </div>
        @endforeach


        <div class="card-soft p-4 mt-4">
            <div class="section-title">Minha anotação sobre esta questão</div>
            <p class="small-muted mb-3">Use este espaço para registrar o motivo de ter favoritado, um erro recorrente, uma dica de resolução ou um ponto que precisa revisar.</p>
            <form method="POST" action="{{ route('student.courses.favorites.note', [$course, $question]) }}">
                @csrf
                @method('PATCH')
                <textarea name="note" class="form-control" rows="4" maxlength="3000" placeholder="Ex.: revisar conceito de choque hipovolêmico; confundi a alternativa B com a D...">{{ old('note', $favorite->note) }}</textarea>
                @error('note')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div class="text-end mt-3">
                    <button class="btn btn-outline-primary">Salvar anotação</button>
                </div>
            </form>
        </div>

        <div class="card-soft p-4 mt-4">
            <div class="section-title">Comentário</div>
            <div class="papirar-katex">{!! $question->commented_answer ?: 'Comentário ainda não cadastrado.' !!}</div>
        </div>

        @include('student.courses.partials.video-lesson', ['question' => $question])

        @if($question->comments->isNotEmpty())
            <div class="card-soft p-4 mt-4">
                <div class="section-title mb-3">Comentários aprovados</div>
                @foreach($question->comments as $comment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="small-muted mb-1">
                            {{ $comment->user->name ?? 'Aluno' }} · {{ optional($comment->created_at)->format('d/m/Y H:i') }}
                        </div>
                        <div>{!! nl2br(e($comment->body ?? $comment->comment ?? '')) !!}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
