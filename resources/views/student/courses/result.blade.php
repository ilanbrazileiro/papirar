@extends('layouts.student')

@section('title', 'Resultado')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Resultado da sessão</h1>
            <p class="page-subtitle">{{ $session->course->title ?? 'Curso' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($session->course)
                <a href="{{ route('student.courses.study', $session->course) }}" class="btn btn-primary">Nova sessão</a>
                <a href="{{ route('student.courses.show', $session->course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stats-card"><div class="label">Total</div><div class="value">{{ $total }}</div></div></div>
        <div class="col-md-3"><div class="stats-card"><div class="label">Corretas</div><div class="value">{{ $correct }}</div></div></div>
        <div class="col-md-3"><div class="stats-card"><div class="label">Erradas</div><div class="value">{{ $incorrect }}</div></div></div>
        <div class="col-md-3"><div class="stats-card"><div class="label">Acerto</div><div class="value">{{ number_format($accuracy, 1, ',', '.') }}%</div></div></div>
    </div>

    <div class="card-soft p-4">
        <div class="section-title">Questões respondidas</div>

        @if($answers->isEmpty())
            <div class="small-muted">Nenhuma resposta registrada.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Questão</th>
                            <th>Disciplina</th>
                            <th>Tópico</th>
                            <th>Resultado</th>
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
                                        <span class="badge text-bg-success">Certa</span>
                                    @else
                                        <span class="badge text-bg-danger">Errada</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
