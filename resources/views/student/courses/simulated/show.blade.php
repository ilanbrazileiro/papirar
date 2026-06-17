@extends('layouts.student')

@section('title', $simulatedExam->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div><h1 class="page-title">{{ $simulatedExam->title }}</h1><p class="page-subtitle">{{ optional($simulatedExam->course)->title }} · Questão {{ $currentPosition }} de {{ $totalQuestions }}</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.simulated.index', $simulatedExam->course_id) }}" class="btn btn-outline-primary">Simulados</a>
            <form method="POST" action="{{ route('student.courses.simulated.finish', $simulatedExam) }}" onsubmit="return confirm('Deseja finalizar o simulado agora?');">@csrf<button type="submit" class="btn btn-danger">Finalizar</button></form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stats-card"><div class="label">Respondidas</div><div class="value">{{ $answeredCount }}/{{ $totalQuestions }}</div></div></div>
        <div class="col-md-4"><div class="stats-card"><div class="label">Questão atual</div><div class="value">{{ $currentPosition }}</div></div></div>
        <div class="col-md-4"><div class="stats-card"><div class="label">Tempo restante</div><div class="value" id="timer" data-seconds="{{ $remainingSeconds }}">--:--</div></div></div>
    </div>

    <div class="card-soft p-4 mb-4">
        <div class="small-muted">{{ optional($question->subject)->name }} @if($question->topic) · {{ $question->topic->name }} @endif</div>
        <h2 class="h5 mt-2">Questão {{ $currentPosition }}</h2>
        <div class="mb-4">{!! $question->statement !!}</div>
        <form method="POST" action="{{ route('student.courses.simulated.save_answer', $simulatedExam) }}">
            @csrf
            <input type="hidden" name="simulated_exam_question_id" value="{{ $currentItem->id }}">
            <input type="hidden" name="next_position" value="{{ optional($nextItem)->position ?: ($currentPosition + 1) }}">
            <div class="list-group mb-4">
                @foreach($question->alternatives as $alternative)
                    <label class="list-group-item">
                        <input type="radio" name="selected_alternative_id" value="{{ $alternative->id }}" class="form-check-input me-2" required @checked($currentItem->selected_alternative_id === $alternative->id)>
                        <strong>{{ $alternative->letter ?? chr(64 + $loop->iteration) }})</strong> {!! $alternative->text !!}
                    </label>
                @endforeach
            </div>
            <div class="d-flex justify-content-between">
                @if($previousItem)<a href="{{ route('student.courses.simulated.show', ['simulatedExam' => $simulatedExam->id, 'question' => $previousItem->position]) }}" class="btn btn-outline-secondary">Anterior</a>@else<span></span>@endif
                <button type="submit" class="btn btn-primary">Salvar e avançar</button>
            </div>
        </form>
    </div>

    <div class="card-soft p-3"><div class="d-flex flex-wrap gap-2">
        @foreach($items as $item)
            <a href="{{ route('student.courses.simulated.show', ['simulatedExam' => $simulatedExam->id, 'question' => $item->position]) }}" class="btn btn-sm {{ $item->position === $currentPosition ? 'btn-primary' : ($item->answered_at ? 'btn-success' : 'btn-outline-secondary') }}">{{ $item->position }}</a>
        @endforeach
    </div></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const timer = document.getElementById('timer');
    if (!timer) return;
    let seconds = parseInt(timer.dataset.seconds || '0', 10);
    function render() {
        const minutes = Math.floor(seconds / 60);
        const rest = seconds % 60;
        timer.textContent = String(minutes).padStart(2, '0') + ':' + String(rest).padStart(2, '0');
        if (seconds <= 0) { window.location.reload(); return; }
        seconds--;
    }
    render(); setInterval(render, 1000);
});
</script>
@endpush
