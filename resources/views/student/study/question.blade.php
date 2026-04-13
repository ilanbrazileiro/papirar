@extends('layouts.student')

@section('title', 'Resolver questão')

@section('content')
    @php
        $userAnswer = $userAnswer ?? null;
        $answered = !is_null($userAnswer);
        $selectedAlternativeId = $userAnswer->selected_alternative_id ?? null;
        $correctAlternative = $question->alternatives->firstWhere('is_correct', true);
        $correctAlternativeId = optional($correctAlternative)->id;
        $correctLetter = optional($correctAlternative)->letter;
        $votes = $question->difficultyVotes ?? collect();
        $counts = [
            'easy' => $votes->where('difficulty_vote', 'easy')->count(),
            'medium' => $votes->where('difficulty_vote', 'medium')->count(),
            'hard' => $votes->where('difficulty_vote', 'hard')->count(),
        ];
    @endphp

    <style>
        .question-shell { display: grid; gap: 1rem; }
        .question-card-modern {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
        }

        .question-statement {
            font-size: 1.08rem;
            line-height: 1.75;
            color: #111827;
        }

        .alternatives-list {
            display: grid;
            gap: 8px;
        }

        .alternative-row {
            display: grid;
            grid-template-columns: 38px 1fr;
            gap: 10px;
            align-items: start;
        }

        .cut-toggle {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: #fff;
            cursor: pointer;
            font-size: 1rem;
            transition: .15s ease;
        }

        .cut-toggle:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .answer-line {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: .15s ease;
        }

        .answer-line:hover {
            background: #f8fafc;
        }

        .answer-line.selected {
            background: #eff6ff;
        }

        .answer-line.correct {
            background: #ecfdf3;
        }

        .answer-line.wrong {
            background: #fef2f2;
        }

        .answer-line.eliminated .answer-text {
            text-decoration: line-through;
            font-weight: 700;
            color: #6b7280;
        }

        .answer-radio {
            display: none;
        }

        .answer-letter {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 999px;
            border: 2px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #334155;
            background: #fff;
            transition: .15s ease;
        }

        .answer-line.selected .answer-letter {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
        }

        .answer-line.correct .answer-letter {
            border-color: #16a34a;
            background: #16a34a;
            color: #fff;
        }

        .answer-line.wrong .answer-letter {
            border-color: #dc2626;
            background: #dc2626;
            color: #fff;
        }

        .answer-text {
            line-height: 1.65;
            color: #111827;
            display: inline;
            padding-top: 4px;
        }

        .inline-flag {
            display: inline-flex;
            align-items: center;
            margin-left: 10px;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .flag-correct {
            background: #dcfce7;
            color: #166534;
        }

        .flag-wrong {
            background: #fee2e2;
            color: #991b1b;
        }

        .flag-gabarito {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .feedback-inline {
            border-radius: 18px;
            padding: 18px 20px;
            border: 1px solid transparent;
        }

        .feedback-inline.correct {
            background: #ecfdf3;
            border-color: #86efac;
            color: #14532d;
        }

        .feedback-inline.wrong {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #7f1d1d;
        }

        .social-comments {
            display: grid;
            gap: 14px;
        }

        .social-comment {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 16px;
            background: #fff;
        }

        .social-avatar {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .social-body {
            flex: 1;
            min-width: 0;
        }

        .social-meta {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .social-name {
            font-weight: 700;
            color: #111827;
        }

        .social-time {
            color: #6b7280;
            font-size: .92rem;
        }

        .comment-box textarea {
            border-radius: 14px;
            min-height: 120px;
        }
    </style>

    <div class="question-shell">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h1 class="page-title">Sessão de estudo</h1>
                <div class="d-flex flex-wrap gap-2">
                    @if($question->corporation)<span class="meta-badge">{{ $question->corporation->name }}</span>@endif
                    @if($question->subject)<span class="meta-badge">{{ $question->subject->name }}</span>@endif
                    @if($question->topic)<span class="meta-badge">{{ $question->topic->name }}</span>@endif
                    @if($question->exam)<span class="meta-badge">{{ $question->exam->title }}</span>@endif
                </div>
            </div>
            <div class="meta-badge">Questão {{ $currentPosition }} de {{ $totalQuestions }}</div>
        </div>

        <div class="question-card-modern">
            <div class="fw-bold mb-3">Leia a questão e escolha a melhor alternativa</div>
            <div class="question-statement">{!! $question->statement !!}</div>
        </div>

        @if(!$answered)
            <form method="POST" action="{{ route('student.study.answer', $session) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">

                <div class="question-card-modern">
                    <div class="alternatives-list">
                        @foreach($question->alternatives->sortBy('letter') as $alternative)
                            <div class="alternative-row">
                                <button type="button" class="cut-toggle" data-cut-toggle title="Cortar alternativa">✂</button>

                                <label class="answer-line" data-answer-card>
                                    <input class="answer-radio" type="radio" name="selected_alternative_id" value="{{ $alternative->id }}" required>
                                    <span class="answer-letter">{{ $alternative->letter }}</span>
                                    <span class="answer-text">{!! trim($alternative->text) !!}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary btn-lg">Responder</button>
                    </div>
                </div>
            </form>
        @else
            <div class="question-card-modern">
                <div class="alternatives-list">
                    @foreach($question->alternatives->sortBy('letter') as $alternative)
                        @php
                            $classes = [];
                            if ($alternative->id == $correctAlternativeId) $classes[] = 'correct';
                            if ($alternative->id == $selectedAlternativeId && !$userAnswer->is_correct) $classes[] = 'wrong';
                            if ($alternative->id == $selectedAlternativeId) $classes[] = 'selected';
                        @endphp

                        <div class="alternative-row">
                            <div></div>

                            <div class="answer-line {{ implode(' ', $classes) }}">
                                <span class="answer-letter">{{ $alternative->letter }}</span>
                                <span class="answer-text">
                                    {!! trim($alternative->text) !!}

                                    @if($alternative->id == $selectedAlternativeId && !$userAnswer->is_correct)
                                        <span class="inline-flag flag-wrong">Sua resposta</span>
                                    @endif

                                    @if($alternative->id == $correctAlternativeId)
                                        <span class="inline-flag flag-gabarito">Gabarito</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="feedback-inline {{ $userAnswer->is_correct ? 'correct' : 'wrong' }}">
                <div class="fw-bold mb-2">
                    {{ $userAnswer->is_correct ? 'Você acertou.' : 'Você errou.' }}
                </div>

                @if(!$userAnswer->is_correct && $correctLetter)
                    <div class="mb-2">A alternativa correta é <strong>{{ $correctLetter }}</strong>.</div>
                @endif

                @if($question->commented_answer)
                    <div>{!! $question->commented_answer !!}</div>
                @endif
            </div>

            <div class="d-flex justify-content-end">
                <form method="POST" action="{{ route('student.study.next', $session) }}">
                    @csrf
                    <button class="btn btn-primary">Próxima questão</button>
                </form>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-soft p-4">
                    <div class="section-title">Classifique a dificuldade</div>
                    <form method="POST" action="{{ route('student.questions.difficulty.store', $question) }}">
                        @csrf
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success text-start" name="difficulty_vote" value="easy">Fácil <span class="float-end">{{ $counts['easy'] }}</span></button>
                            <button class="btn btn-outline-warning text-start" name="difficulty_vote" value="medium">Média <span class="float-end">{{ $counts['medium'] }}</span></button>
                            <button class="btn btn-outline-danger text-start" name="difficulty_vote" value="hard">Difícil <span class="float-end">{{ $counts['hard'] }}</span></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-soft p-4 comment-box">
                    <div class="section-title">Enviar comentário</div>
                    <form method="POST" action="{{ route('student.questions.comments.store', $question) }}">
                        @csrf
                        <textarea name="comment" class="form-control" placeholder="Seu comentário vai para moderação antes de aparecer."></textarea>
                        <button type="submit" class="btn btn-primary mt-3">Enviar comentário</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-soft p-4">
            <div class="section-title">Comentários dos alunos</div>

            <div class="social-comments">
                @forelse($question->comments as $comment)
                    @php
                        $name = $comment->user->name ?? 'Aluno';
                        $initial = mb_strtoupper(mb_substr($name, 0, 1));
                    @endphp

                    <div class="social-comment">
                        <div class="social-avatar">{{ $initial }}</div>

                        <div class="social-body">
                            <div class="social-meta">
                                <span class="social-name">{{ $name }}</span>
                                <span class="social-time">{{ $comment->created_at?->format('d/m/Y H:i') }}</span>
                            </div>

                            <div>{{ $comment->comment }}</div>
                        </div>
                    </div>
                @empty
                    <div class="small-muted">Ainda não há comentários aprovados para esta questão.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-answer-card]').forEach(function (card) {
    card.addEventListener('click', function () {
        document.querySelectorAll('[data-answer-card]').forEach(function (item) {
            item.classList.remove('selected');
        });

        const input = card.querySelector('input[type="radio"]');
        if (input) {
            input.checked = true;
            card.classList.add('selected');
        }
    });
});

document.querySelectorAll('[data-cut-toggle]').forEach(function (button) {
    button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const row = button.closest('.alternative-row');
        const card = row ? row.querySelector('[data-answer-card]') : null;

        if (!card) return;
        card.classList.toggle('eliminated');
    });
});
</script>
@endpush
