@extends('layouts.student')

@section('title', 'Resolver questão')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Sessão de estudo</h1>
            <div class="d-flex flex-wrap gap-2">
                <span class="meta-badge">{{ $question->corporation->name }}</span>
                <span class="meta-badge">{{ $question->subject->name }}</span>

                @if($question->topic)
                    <span class="meta-badge">{{ $question->topic->name }}</span>
                @endif

                @if($question->exam)
                    <span class="meta-badge">{{ $question->exam->title }}</span>
                @endif
            </div>
        </div>

        <div class="progress-wrap">
            <div class="text-end">
                <div class="fw-semibold">Questão {{ $current }} de {{ $total }}</div>
                <div class="text-muted small">{{ $progress }}% concluído</div>
            </div>
            <div style="width: 220px;">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-soft p-4 p-md-5 mb-4">
        <div class="question-statement">
            {!! $question->statement !!}
        </div>
    </div>

    @php
        $answered = $answered ?? false;
    @endphp

    @if(!$answered)
        <form method="POST" action="{{ route('student.study.answer', $session) }}">
            @csrf
            <input type="hidden" name="session_question_id" value="{{ $sessionQuestion->id }}">

            <div class="d-grid gap-3">
                @foreach($question->alternatives as $alternative)
                    <label class="alternative-option p-3 p-md-4" data-option>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="pt-1">
                                <input
                                    class="form-check-input alternative-radio"
                                    type="radio"
                                    name="alternative_id"
                                    value="{{ $alternative->id }}"
                                    required
                                >
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1">{{ $alternative->letter }})</div>
                                <div>{!! $alternative->text !!}</div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    Responder
                </button>
            </div>
        </form>
    @else
        <div class="d-grid gap-3 mb-4">
            @foreach($question->alternatives as $alternative)
                @php
                    $class = '';

                    if ($alternative->id == $correctAlternativeId) {
                        $class = 'correct';
                    } elseif ($alternative->id == $selectedAlternativeId && !$isCorrect) {
                        $class = 'wrong';
                    }
                @endphp

                <div class="alternative-option {{ $class }} p-3 p-md-4">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="pt-1">
                            <input
                                class="form-check-input alternative-radio"
                                type="radio"
                                disabled
                                @checked($alternative->id == $selectedAlternativeId)
                            >
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1">{{ $alternative->letter }})</div>
                            <div>{!! $alternative->text !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="{{ $isCorrect ? 'feedback-box-success' : 'feedback-box-danger' }} p-4 mb-4">
            <div class="fw-bold mb-2">
                {{ $isCorrect ? 'Resposta correta.' : 'Resposta incorreta.' }}
            </div>

            @if($question->commented_answer)
                <div class="mt-3">
                    <div class="fw-semibold mb-2">Comentário da questão</div>
                    <div>{!! $question->commented_answer !!}</div>
                </div>
            @endif
        </div>

        <div class="d-flex justify-content-end">
            <form method="POST" action="{{ route('student.study.next', $session) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    Próxima questão
                </button>
            </form>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-option]').forEach(function (label) {
        label.addEventListener('click', function () {
            document.querySelectorAll('[data-option]').forEach(function (item) {
                item.classList.remove('selected');
            });

            label.classList.add('selected');

            const input = label.querySelector('input[type="radio"]');
            if (input) {
                input.checked = true;
            }
        });
    });
</script>
@endpush
