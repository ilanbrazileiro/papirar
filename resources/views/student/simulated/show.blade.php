@extends('layouts.student')

@section('title', 'Resolver simulado')

@section('content')
    @php
        $selectedAlternativeId = $currentItem->selected_alternative_id ?? null;
    @endphp

    <style>
        .sim-shell { display: grid; grid-template-columns: 300px 1fr; gap: 18px; }
        .sim-sidebar, .sim-main-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15,23,42,.04);
        }
        .sim-sidebar { padding: 18px; align-self: start; position: sticky; top: 20px; }
        .sim-main-card { padding: 22px; }
        .sim-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
        .sim-nav-number {
            display: inline-flex; align-items: center; justify-content: center;
            height: 42px; border-radius: 12px; border: 1px solid #d1d5db;
            text-decoration: none; color: #111827; font-weight: 700; background: #fff;
        }
        .sim-nav-number.current { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }
        .sim-nav-number.answered { border-color: #16a34a; background: #ecfdf3; color: #166534; }
        .question-statement { font-size: 1.08rem; line-height: 1.75; color: #111827; }
        .alternatives-list { display: grid; gap: 8px; }
        .alternative-row { display: grid; grid-template-columns: 38px 1fr; gap: 10px; align-items: start; }
        .cut-toggle {
            width: 38px; height: 38px; border-radius: 10px; border: 1px solid #d1d5db;
            background: #fff; cursor: pointer; font-size: 1rem; transition: .15s ease;
        }
        .cut-toggle:hover { background: #f9fafb; border-color: #9ca3af; }
        .answer-line {
            display: flex; align-items: flex-start; gap: 12px; padding: 10px 12px;
            border-radius: 12px; cursor: pointer; transition: .15s ease;
        }
        .answer-line:hover { background: #f8fafc; }
        .answer-line.selected { background: #eff6ff; }
        .answer-line.eliminated .answer-text { text-decoration: line-through; font-weight: 700; color: #6b7280; }
        .answer-radio { display: none; }
        .answer-letter {
            width: 34px; height: 34px; min-width: 34px; border-radius: 999px;
            border: 2px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; color: #334155; background: #fff; transition: .15s ease;
        }
        .answer-line.selected .answer-letter { border-color: #2563eb; background: #2563eb; color: #fff; }
        .answer-text { line-height: 1.65; color: #111827; display: inline; padding-top: 4px; }
        @media (max-width: 992px) {
            .sim-shell { grid-template-columns: 1fr; }
            .sim-sidebar { position: static; }
        }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $simulatedExam->title }}</h1>
            <p class="page-subtitle">Navegue livremente pelas questões e finalize só quando quiser.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="meta-badge">Respondidas: {{ $answeredCount }}/{{ $totalQuestions }}</span>
            @if($simulatedExam->finished_at)
                <span class="meta-badge">Finalizado</span>
            @endif
        </div>
    </div>

    <div class="sim-shell">
        <aside class="sim-sidebar">
            <div class="fw-bold mb-2">Mapa do simulado</div>
            <div class="small-muted mb-3">Clique no número para ir direto à questão.</div>

            <div class="sim-grid mb-4">
                @foreach($items as $item)
                    <a
                        href="{{ route('student.simulated.show', ['simulatedExam' => $simulatedExam->id, 'question' => $item->position]) }}"
                        class="sim-nav-number {{ $item->position === $currentPosition ? 'current' : '' }} {{ $item->answered_at ? 'answered' : '' }}"
                    >
                        {{ $item->position }}
                    </a>
                @endforeach
            </div>

            @if(is_null($simulatedExam->finished_at))
                <form method="POST" action="{{ route('student.simulated.finish', $simulatedExam) }}">
                    @csrf
                    <button class="btn btn-outline-danger w-100" onclick="return confirm('Deseja finalizar o simulado agora?');">
                        Finalizar simulado
                    </button>
                </form>
            @else
                <a href="{{ route('student.simulated.result', $simulatedExam) }}" class="btn btn-primary w-100">Ver resultado</a>
            @endif
        </aside>

        <section class="sim-main-card">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <div class="fw-bold">Questão {{ $currentPosition }} de {{ $totalQuestions }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @if($question->corporation)<span class="meta-badge">{{ $question->corporation->name }}</span>@endif
                        @if($question->subject)<span class="meta-badge">{{ $question->subject->name }}</span>@endif
                        @if($question->topic)<span class="meta-badge">{{ $question->topic->name }}</span>@endif
                        @if($question->exam)<span class="meta-badge">{{ $question->exam->title }}</span>@endif
                    </div>
                </div>
            </div>

            <div class="question-statement mb-4">{!! $question->statement !!}</div>

            @if(is_null($simulatedExam->finished_at))
                <form method="POST" action="{{ route('student.simulated.save_answer', $simulatedExam) }}">
                    @csrf
                    <input type="hidden" name="simulated_exam_question_id" value="{{ $currentItem->id }}">

                    <div class="alternatives-list">
                        @foreach($question->alternatives->sortBy('letter') as $alternative)
                            <div class="alternative-row">
                                <button type="button" class="cut-toggle" data-cut-toggle title="Cortar alternativa">✂</button>

                                <label class="answer-line {{ $selectedAlternativeId == $alternative->id ? 'selected' : '' }}" data-answer-card>
                                    <input
                                        class="answer-radio"
                                        type="radio"
                                        name="selected_alternative_id"
                                        value="{{ $alternative->id }}"
                                        @checked($selectedAlternativeId == $alternative->id)
                                        required
                                    >
                                    <span class="answer-letter">{{ $alternative->letter }}</span>
                                    <span class="answer-text">{!! trim($alternative->text) !!}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mt-4">
                        <div class="d-flex gap-2">
                            @if($previousItem)
                                <a href="{{ route('student.simulated.show', ['simulatedExam' => $simulatedExam->id, 'question' => $previousItem->position]) }}" class="btn btn-outline-secondary">
                                    Anterior
                                </a>
                            @endif

                            @if($nextItem)
                                <button type="submit" name="next_position" value="{{ $nextItem->position }}" class="btn btn-primary">
                                    Salvar e avançar
                                </button>
                            @else
                                <button type="submit" name="next_position" value="{{ $currentPosition }}" class="btn btn-primary">
                                    Salvar resposta
                                </button>
                            @endif
                        </div>

                        <div>
                            <button type="submit" name="next_position" value="{{ $currentPosition }}" class="btn btn-outline-primary">
                                Salvar sem avançar
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="alert alert-info rounded-4">
                    Este simulado já foi finalizado. Vá para o resultado para revisar seu desempenho.
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-answer-card]').forEach(function (card) {
    card.addEventListener('click', function () {
        const container = card.closest('form');
        if (!container) return;

        container.querySelectorAll('[data-answer-card]').forEach(function (item) {
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
