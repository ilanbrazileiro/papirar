@extends('layouts.admin')

@section('title', 'Relatórios por curso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Relatórios por curso</h1>
            <p class="text-muted mb-0">Visão comercial e operacional dos cursos vendidos no Papirar.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data final</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Curso</label>
                    <select name="course_id" class="form-control">
                        <option value="">Todos os cursos</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) $courseId === (int) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totals['active_accesses'] }}</h3>
                    <p>Acessos ativos</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ {{ number_format($totals['revenue'], 2, ',', '.') }}</h3>
                    <p>Receita paga no período</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totals['pending_payments'] }}</h3>
                    <p>Pagamentos pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totals['answers'] }}</h3>
                    <p>Respostas em cursos</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Top cursos por receita</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th class="text-right">Receita</th>
                                <th class="text-right">Pagos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topRevenueCourses as $row)
                                <tr>
                                    <td>{{ $row['course']->title }}</td>
                                    <td class="text-right">R$ {{ number_format($row['revenue'], 2, ',', '.') }}</td>
                                    <td class="text-right">{{ $row['paid_payments'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center py-3">Sem dados no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><strong>Top cursos por engajamento</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th class="text-right">Respostas</th>
                                <th class="text-right">Aproveitamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topEngagementCourses as $row)
                                <tr>
                                    <td>{{ $row['course']->title }}</td>
                                    <td class="text-right">{{ $row['answers'] }}</td>
                                    <td class="text-right">{{ number_format($row['accuracy'], 1, ',', '.') }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center py-3">Sem dados no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Resumo por curso</strong>
            <span class="text-muted small">Período: {{ $dateFrom->format('d/m/Y') }} a {{ $dateTo->format('d/m/Y') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th class="text-center">Acessos ativos</th>
                        <th class="text-center">Trials</th>
                        <th class="text-center">Pagos</th>
                        <th class="text-center">Pendentes</th>
                        <th class="text-right">Receita</th>
                        <th class="text-center">Respostas</th>
                        <th class="text-center">Simulados</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courseRows as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['course']->title }}</strong><br>
                                <small class="text-muted">{{ $row['course']->active ? 'ativo' : 'inativo' }} · {{ $row['course']->is_public ? 'público' : 'interno' }}</small>
                            </td>
                            <td class="text-center">{{ $row['active_accesses'] }}</td>
                            <td class="text-center">{{ $row['trial_accesses'] }}</td>
                            <td class="text-center">{{ $row['paid_accesses'] }}</td>
                            <td class="text-center">{{ $row['pending_payments'] }}</td>
                            <td class="text-right">R$ {{ number_format($row['revenue'], 2, ',', '.') }}</td>
                            <td class="text-center">{{ $row['answers'] }}</td>
                            <td class="text-center">{{ $row['simulated_finished'] }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.reports.courses.show', ['course' => $row['course']->id, 'date_from' => $dateFrom->format('Y-m-d'), 'date_to' => $dateTo->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-primary">Detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Nenhum curso encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
