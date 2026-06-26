@extends('layouts.student')

@section('title', $simulatedExam->title ?? 'Simulado')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $simulatedExam->title }}</strong>
                        <div class="small text-muted">Questão {{ $currentPosition }} de {{ $totalQuestions }}</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Tempo restante</div>
                        <div id="simulated-timer" class="h5 mb-0" data-remaining-seconds="{{ $remainingSeconds }}">--:--:--</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-secondary">{{ optional($question->subject)->name ?? 'Sem disciplina' }}</span>
                        @if($question->topic)
                            <span class="badge bg-light text-dark border">{{ $question->topic->name }}</span>
                        @endif
                    </div>

                    <div class="mb-4">
                        {!! $question->statement !!}
                    </div>

                    <form method="POST" action="{{ route('student.simulated.save_answer', $simulatedExam) }}">
                        @csrf
                        <input type="hidden" name="simulated_exam_question_id" value="{{ $currentItem->id }}">
                        <input type="hidden" name="next_position" value="{{ $nextItem ? $nextItem->position : $currentPosition }}">

                        @foreach($question->alternatives as $alternative)
                            <div class="form-check border rounded p-3 mb-2">
                                <input class="form-check-input ms-0 me-2" type="radio" name="selected_alternative_id" id="alternative_{{ $alternative->id }}" value="{{ $alternative->id }}" @checked((int) $currentItem->selected_alternative_id === (int) $alternative->id) required>
                                <label class="form-check-label w-100" for="alternative_{{ $alternative->id }}">
                                    <strong>{{ $alternative->letter }}.</strong> {!! $alternative->text !!}
                                </label>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                @if($previousItem)
                                    <a href="{{ route('student.simulated.show', ['simulatedExam' => $simulatedExam, 'question' => $previousItem->position]) }}" class="btn btn-outline-secondary">Anterior</a>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Salvar resposta</button>
                                @if($nextItem)
                                    <button type="submit" class="btn btn-outline-primary">Salvar e próxima</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-3">
                <div class="card-header"><strong>Mapa do simulado</strong></div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Respondidas: {{ $answeredCount }} / {{ $totalQuestions }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($items as $item)
                            @php
                                $btnClass = 'btn-outline-secondary';
                                if ($item->answered_at) {
                                    $btnClass = 'btn-success';
                                }
                                if ((int) $item->position === (int) $currentPosition) {
                                    $btnClass = 'btn-primary';
                                }
                            @endphp
                            <a href="{{ route('student.simulated.show', ['simulatedExam' => $simulatedExam, 'question' => $item->position]) }}" class="btn btn-sm {{ $btnClass }}">{{ $item->position }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('student.simulated.finish', $simulatedExam) }}" id="finish-simulated-form" onsubmit="return confirm('Deseja finalizar o simulado? As questões não respondidas ficarão em branco.');">
                @csrf
                <button type="submit" class="btn btn-danger w-100">Finalizar simulado</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timer = document.getElementById('simulated-timer');
    const finishForm = document.getElementById('finish-simulated-form');
    let remaining = parseInt(timer.dataset.remainingSeconds || '0', 10);
    let submitted = false;

    function format(seconds) {
        const h = Math.floor(seconds / 3600).toString().padStart(2, '0');
        const m = Math.floor((seconds % 3600) / 60).toString().padStart(2, '0');
        const s = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    function tick() {
        timer.textContent = format(Math.max(0, remaining));

        if (remaining <= 0 && !submitted) {
            submitted = true;
            finishForm.submit();
            return;
        }

        remaining -= 1;
        setTimeout(tick, 1000);
    }

    tick();
});
</script>
@endsection
