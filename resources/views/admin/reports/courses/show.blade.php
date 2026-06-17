@extends('layouts.admin')

@section('title', 'Relatório do curso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Relatório do curso</h1>
            <p class="text-muted mb-0">{{ $course->title }}</p>
        </div>
        <a href="{{ route('admin.reports.courses.index', ['date_from' => $dateFrom->format('Y-m-d'), 'date_to' => $dateTo->format('Y-m-d')]) }}" class="btn btn-outline-primary">Voltar</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data final</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary btn-block">Atualizar relatório</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-info"><div class="inner"><h3>{{ $row['active_accesses'] }}</h3><p>Acessos ativos</p></div></div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-success"><div class="inner"><h3>R$ {{ number_format($row['revenue'], 2, ',', '.') }}</h3><p>Receita paga</p></div></div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-primary"><div class="inner"><h3>{{ $row['answers'] }}</h3><p>Respostas no curso</p></div></div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-warning"><div class="inner"><h3>{{ number_format($row['accuracy'], 1, ',', '.') }}%</h3><p>Aproveitamento médio</p></div></div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Desempenho por disciplina</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Disciplina</th>
                                <th class="text-center">Respondidas</th>
                                <th class="text-center">Certas</th>
                                <th class="text-right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->subject_name ?? 'Sem disciplina' }}</td>
                                    <td class="text-center">{{ $subject->answered }}</td>
                                    <td class="text-center">{{ $subject->correct }}</td>
                                    <td class="text-right">{{ number_format($subject->accuracy, 1, ',', '.') }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sem respostas no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Últimas respostas</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Questão</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAnswers as $answer)
                                <tr>
                                    <td>{{ $answer->user_name ?? 'Aluno' }}<br><small class="text-muted">{{ $answer->user_email }}</small></td>
                                    <td>#{{ $answer->question_id }}<br><small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($answer->statement), 70) }}</small></td>
                                    <td>{!! $answer->is_correct ? '<span class="badge badge-success">Certa</span>' : '<span class="badge badge-danger">Errada</span>' !!}</td>
                                    <td>{{ \Carbon\Carbon::parse($answer->answered_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sem respostas recentes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Pagamentos do período</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Status</th>
                                <th>Ciclo</th>
                                <th class="text-right">Valor</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->user_name ?? 'Aluno' }}<br><small class="text-muted">{{ $payment->user_email }}</small></td>
                                    <td>{{ $payment->status }}</td>
                                    <td>{{ $payment->billing_cycle ?? '-' }}</td>
                                    <td class="text-right">R$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</td>
                                    <td>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Sem pagamentos no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Últimos acessos</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Status</th>
                                <th>Tipo</th>
                                <th>Fim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accesses as $access)
                                <tr>
                                    <td>{{ $access->user_name ?? 'Aluno' }}<br><small class="text-muted">{{ $access->user_email }}</small></td>
                                    <td>{{ $access->status }}</td>
                                    <td>{{ $access->access_type }}</td>
                                    <td>{{ $access->ends_at ? \Carbon\Carbon::parse($access->ends_at)->format('d/m/Y H:i') : 'Sem limite' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sem acessos cadastrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
