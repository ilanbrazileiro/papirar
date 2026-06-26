@extends('layouts.student')

@section('title', 'Resultado do simulado')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Resultado do simulado</h1>
            <p class="text-muted mb-0">{{ $simulatedExam->title }}</p>
        </div>
        <a href="{{ route('student.simulated.index') }}" class="btn btn-outline-secondary">Voltar aos simulados</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100"><div class="card-body"><div class="text-muted small">Questões</div><div class="h3 mb-0">{{ $simulatedExam->total_questions }}</div></div></div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100"><div class="card-body"><div class="text-muted small">Acertos</div><div class="h3 mb-0">{{ $simulatedExam->correct_answers }}</div></div></div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100"><div class="card-body"><div class="text-muted small">Em branco</div><div class="h3 mb-0">{{ $blankCount }}</div></div></div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100"><div class="card-body"><div class="text-muted small">Aproveitamento</div><div class="h3 mb-0">{{ number_format((float) $simulatedExam->accuracy, 2, ',', '.') }}%</div></div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Desempenho por disciplina</strong></div>
        <div class="card-body">
            @if($subjectStats->count())
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Disciplina</th>
                                <th>Total</th>
                                <th>Respondidas</th>
                                <th>Em branco</th>
                                <th>Acertos</th>
                                <th>Aproveitamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectStats as $subjectName => $stats)
                                <tr>
                                    <td>{{ $subjectName }}</td>
                                    <td>{{ $stats['total'] }}</td>
                                    <td>{{ $stats['answered'] }}</td>
                                    <td>{{ $stats['blank'] }}</td>
                                    <td>{{ $stats['correct'] }}</td>
                                    <td>{{ number_format((float) $stats['accuracy'], 2, ',', '.') }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">Sem dados para exibir.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Revisão das questões</strong></div>
        <div class="card-body">
            @foreach($items as $item)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Questão {{ $item->position }}</strong>
                        @if(is_null($item->answered_at))
                            <span class="badge bg-secondary">Em branco</span>
                        @elseif($item->is_correct)
                            <span class="badge bg-success">Correta</span>
                        @else
                            <span class="badge bg-danger">Errada</span>
                        @endif
                    </div>

                    <div class="small text-muted mb-2">
                        {{ optional($item->question->subject)->name ?? 'Sem disciplina' }}
                        @if($item->question->topic)
                            — {{ $item->question->topic->name }}
                        @endif
                    </div>

                    <div class="mb-3">{!! $item->question->statement !!}</div>

                    <div class="mb-2">
                        <strong>Sua resposta:</strong>
                        @if($item->selectedAlternative)
                            {{ $item->selectedAlternative->letter }}. {!! $item->selectedAlternative->text !!}
                        @else
                            <span class="text-muted">Em branco</span>
                        @endif
                    </div>

                    @php
                        $correctAlternative = $item->question->alternatives->firstWhere('is_correct', true);
                    @endphp
                    @if($correctAlternative)
                        <div class="mb-2">
                            <strong>Gabarito:</strong> {{ $correctAlternative->letter }}. {!! $correctAlternative->text !!}
                        </div>
                    @endif

                    @if($item->question->commented_answer)
                        <div class="alert alert-light border mt-3 mb-0">
                            <strong>Comentário:</strong><br>
                            {!! $item->question->commented_answer !!}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
