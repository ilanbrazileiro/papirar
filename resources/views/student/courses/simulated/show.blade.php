@extends('layouts.student')

@section('title', 'Simulado em andamento')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $simulatedExam->title }}</h1>
            <p class="page-subtitle">
                {{ $course->title }} · Questão {{ $currentPosition }} de {{ $totalQuestions }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge text-bg-warning fs-6 px-3 py-2" id="simulated-timer" data-remaining="{{ $remainingSeconds }}">
                Tempo restante: carregando...
            </span>

            <form method="POST" action="{{ route('student.courses.simulated.finish', [$course, $simulatedExam]) }}" onsubmit="return confirm('Deseja finalizar o simulado agora? Questões sem resposta ficarão em branco.');">
                @csrf
                <button class="btn btn-outline-danger">Finalizar simulado</button>
            </form>
        </div>
    </div>

     <div class="row g-4">
        <div class="col-lg-8">
            <div class="question-card mb-4">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($question->subject)
                        <span class="meta-badge">Disciplina: {{ $question->subject->name }}</span>
                    @endif
                    @if($question->topic)
                        <span class="meta-badge">Tópico: {{ $question->topic->name }}</span>
                    @endif
                    @if($question->difficulty)
                        <span class="meta-badge">Dificuldade: {{ $question->difficulty }}</span>
                    @endif
                </div>

                <div class="mb-4 papirar-katex">
                    {!! $question->statement !!}
                </div>

                <form method="POST" action="{{ route('student.courses.simulated.save_answer', [$course, $simulatedExam]) }}">
                    @csrf
                    <input type="hidden" name="simulated_exam_question_id" value="{{ $currentItem->id }}">
                    <input type="hidden" name="next_position" value="{{ optional($nextItem)->position ?? ($currentPosition + 1) }}">

                    @foreach($question->alternatives->sortBy('letter') as $alternative)
                        <label class="alt-card d-flex align-items-start gap-2 mb-2">
                            <input type="radio" name="selected_alternative_id" value="{{ $alternative->id }}" required class="mt-1" @checked((int) $currentItem->selected_alternative_id === (int) $alternative->id)>
                            <span><strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}</span>
                        </label>
                    @endforeach

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-4">
                        <div class="d-flex flex-wrap gap-2">
                            @if($previousItem)
                                <a href="{{ route('student.courses.simulated.show', [$course, $simulatedExam, 'question' => $previousItem->position]) }}" class="btn btn-outline-primary">Anterior</a>
                            @endif
                            @if($nextItem)
                                <a href="{{ route('student.courses.simulated.show', [$course, $simulatedExam, 'question' => $nextItem->position]) }}" class="btn btn-outline-primary">Próxima sem salvar</a>
                            @endif
                        </div>
                        <button class="btn btn-primary">Salvar resposta e avançar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Progresso</div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Respondidas</span><strong>{{ $answeredCount }} de {{ $totalQuestions }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span>Questão atual</span><strong>{{ $currentPosition }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span>Em branco</span><strong>{{ max(0, $totalQuestions - $answeredCount) }}</strong></div>
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Mapa do simulado</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($items as $item)
                        @php
                            $isCurrent = (int) $item->id === (int) $currentItem->id;
                            $isAnswered = ! is_null($item->answered_at);
                        @endphp
                        <a href="{{ route('student.courses.simulated.show', [$course, $simulatedExam, 'question' => $item->position]) }}" class="btn btn-sm {{ $isCurrent ? 'btn-primary' : ($isAnswered ? 'btn-outline-success' : 'btn-outline-secondary') }}" style="min-width: 42px;">
                            {{ $item->position }}
                        </a>
                    @endforeach
                </div>
                <div class="small-muted mt-3">No simulado, o gabarito e os comentários aparecem apenas no resultado final.</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const timer = document.getElementById('simulated-timer');
    if (!timer) return;
    let remaining = parseInt(timer.dataset.remaining || '0', 10);
    function renderTimer() {
        if (remaining < 0) remaining = 0;
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;
        timer.textContent = 'Tempo restante: ' + [String(hours).padStart(2, '0'), String(minutes).padStart(2, '0'), String(seconds).padStart(2, '0')].join(':');
        if (remaining <= 0) { window.location.reload(); return; }
        remaining--;
    }
    renderTimer();
    setInterval(renderTimer, 1000);
});
</script>
@endpush
