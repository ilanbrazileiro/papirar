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
        <a href="{{ $session->course ? route('student.courses.show', $session->course) : route('student.courses.index') }}" class="btn btn-outline-primary">Voltar ao curso</a>
    </div>

    <div class="question-card mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @if($question->difficulty)
                <span class="meta-badge">Dificuldade: {{ $question->difficulty }}</span>
            @endif
            @if($question->sourceMaterial)
                <span class="meta-badge">Fonte: {{ $question->sourceMaterial->title }}</span>
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

            <form method="POST" action="{{ route('student.course-study.next', $session) }}" class="mt-4 text-end">
                @csrf
                <button class="btn btn-primary">Próxima questão</button>
            </form>
        @else
            <form method="POST" action="{{ route('student.course-study.answer', $session) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">

                @foreach($question->alternatives->sortBy('letter') as $alternative)
                    <label class="alt-card d-block mb-2">
                        <input type="radio" name="selected_alternative_id" value="{{ $alternative->id }}" required class="me-2">
                        <strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}
                    </label>
                @endforeach

                <div class="text-end mt-4">
                    <button class="btn btn-primary">Responder</button>
                </div>
            </form>
        @endif
    </div>
@endsection
