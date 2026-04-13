@extends('layouts.student')

@section('title', 'Resultado da sessão')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Resultado da sessão</h1>
        <p class="page-subtitle">Veja o desempenho desta sessão e identifique seus pontos fracos.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="label">Questões</div>
                <div class="value">{{ $total }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="label">Acertos</div>
                <div class="value">{{ $correct }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="label">Erros</div>
                <div class="value">{{ $incorrect }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="label">Acurácia</div>
                <div class="value">{{ number_format((float) $accuracy, 2, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    <div class="card-soft p-4">
        <div class="section-title">Resumo por questão</div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Questão</th>
                        <th>Disciplina</th>
                        <th>Assunto</th>
                        <th>Resposta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($answers as $answer)
                        <tr>
                            <td>#{{ $answer->question_id }}</td>
                            <td>{{ $answer->question->subject->name ?? '-' }}</td>
                            <td>{{ $answer->question->topic->name ?? '-' }}</td>
                            <td>
                                @if($answer->is_correct)
                                    <span class="badge text-bg-success">Correta</span>
                                @else
                                    <span class="badge text-bg-danger">Errada</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4 gap-2">
            <a href="{{ route('student.study.index') }}" class="btn btn-outline-secondary">Nova sessão</a>
            <a href="{{ route('student.simulated.index') }}" class="btn btn-primary">Ir para simulados</a>
        </div>
    </div>
@endsection
