@extends('layouts.student')

@section('title', 'Resultado do simulado')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div><h1 class="page-title">Resultado do simulado</h1><p class="page-subtitle">{{ $simulatedExam->title }} · {{ optional($simulatedExam->course)->title }}</p></div>
        <div class="d-flex flex-wrap gap-2"><a href="{{ route('student.courses.simulated.index', $simulatedExam->course_id) }}" class="btn btn-outline-primary">Simulados</a><a href="{{ route('student.courses.show', $simulatedExam->course_id) }}" class="btn btn-primary">Voltar ao curso</a></div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stats-card"><div class="label">Questões</div><div class="value">{{ $items->count() }}</div></div></div>
        <div class="col-md-3"><div class="stats-card"><div class="label">Respondidas</div><div class="value">{{ $answeredCount }}</div></div></div>
        <div class="col-md-3"><div class="stats-card"><div class="label">Em branco</div><div class="value">{{ $blankCount }}</div></div></div>
        <div class="col-md-3"><div class="stats-card"><div class="label">Acerto</div><div class="value">{{ number_format((float) $simulatedExam->accuracy, 2, ',', '.') }}%</div></div></div>
    </div>
    <div class="card-soft p-4 mb-4">
        <div class="section-title">Desempenho por disciplina</div>
        @if($subjectStats->isEmpty())<div class="small-muted">Nenhum dado para exibir.</div>@else
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Disciplina</th><th>Total</th><th>Respondidas</th><th>Em branco</th><th>Corretas</th><th>Acerto</th></tr></thead><tbody>
                @foreach($subjectStats as $subjectName => $stats)
                    <tr><td>{{ $subjectName }}</td><td>{{ $stats['total'] }}</td><td>{{ $stats['answered'] }}</td><td>{{ $stats['blank'] }}</td><td>{{ $stats['correct'] }}</td><td>{{ number_format((float) $stats['accuracy'], 2, ',', '.') }}%</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </div>
    <div class="card-soft p-4">
        <div class="section-title">Questões do simulado</div>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Disciplina</th><th>Tópico</th><th>Status</th></tr></thead><tbody>
            @foreach($items as $item)
                <tr><td>{{ $item->position }}</td><td>{{ optional($item->question->subject)->name ?: '-' }}</td><td>{{ optional($item->question->topic)->name ?: '-' }}</td><td>@if(is_null($item->answered_at))<span class="badge bg-secondary">Em branco</span>@elseif($item->is_correct)<span class="badge bg-success">Correta</span>@else<span class="badge bg-danger">Errada</span>@endif</td></tr>
            @endforeach
        </tbody></table></div>
    </div>
@endsection
