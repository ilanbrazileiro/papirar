@extends('layouts.admin')

@section('title', 'Questões por Curso | Papirar')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <h1 class="h3 mb-1">Questões por Curso</h1>
        <p class="text-muted mb-0">
            O total disponível ao aluno considera questões publicadas e revisadas.
        </p>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Filtros</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.course-questions.index') }}">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="course_id">Curso</label>
                        <select name="course_id" id="course_id" class="form-control">
                            <option value="">Todos os cursos</option>
                            @foreach($courseOptions as $courseOption)
                                <option value="{{ $courseOption->id }}" @selected($selectedCourseId === $courseOption->id)>
                                    {{ $courseOption->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group form-check">
                        <input type="hidden" name="active_only" value="0">
                        <input type="checkbox" class="form-check-input" id="active_only" name="active_only" value="1" @checked($activeOnly)>
                        <label class="form-check-label" for="active_only">Somente ativos</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group form-check">
                        <input type="hidden" name="public_only" value="0">
                        <input type="checkbox" class="form-check-input" id="public_only" name="public_only" value="1" @checked($publicOnly)>
                        <label class="form-check-label" for="public_only">Somente públicos</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group form-check">
                        <input type="hidden" name="show_empty_subjects" value="0">
                        <input type="checkbox" class="form-check-input" id="show_empty_subjects" name="show_empty_subjects" value="1" @checked($showEmptySubjects)>
                        <label class="form-check-label" for="show_empty_subjects">Mostrar disciplinas sem questões</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('admin.reports.course-questions.index') }}" class="btn btn-default">Limpar</a>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($totals['courses'], 0, ',', '.') }}</h3><p>Cursos no relatório</p></div>
            <div class="icon"><i class="fas fa-graduation-cap"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($totals['available'], 0, ',', '.') }}</h3><p>Disponíveis ao aluno</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ number_format($totals['draft'], 0, ',', '.') }}</h3><p>Questões em rascunho</p></div>
            <div class="icon"><i class="fas fa-edit"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ number_format($totals['empty_subjects'], 0, ',', '.') }}</h3><p>Disciplinas sem questões</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quantidade de questões por curso e disciplina</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th class="text-center">Disciplinas</th>
                    <th class="text-center">Disponíveis</th>
                    <th class="text-center">Publicadas</th>
                    <th class="text-center">Revisadas</th>
                    <th class="text-center">Rascunhos</th>
                    <th class="text-center">Arquivadas</th>
                    <th class="text-center">Total cadastrado</th>
                    <th class="text-center">Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td>
                            <strong>{{ $row['course']->title }}</strong><br>
                            <small class="text-muted">
                                {{ $row['course']->corporation->name ?? 'Sem corporação' }}
                                @if($row['course']->inherit_exam_scope)
                                    · escopo herdado do concurso
                                @else
                                    · escopo próprio
                                @endif
                            </small>
                        </td>
                        <td class="text-center">{{ $row['subject_count'] }}</td>
                        <td class="text-center"><span class="badge badge-success">{{ $row['available'] }}</span></td>
                        <td class="text-center">{{ $row['published'] }}</td>
                        <td class="text-center">{{ $row['reviewed'] }}</td>
                        <td class="text-center">{{ $row['draft'] }}</td>
                        <td class="text-center">{{ $row['archived'] }}</td>
                        <td class="text-center">{{ $row['total'] }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#course-subjects-{{ $index }}" aria-expanded="false">
                                <i class="fas fa-list"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse bg-light" id="course-subjects-{{ $index }}">
                        <td colspan="9" class="p-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white mb-0">
                                    <thead>
                                        <tr>
                                            <th>Disciplina</th>
                                            <th class="text-center">Disponíveis</th>
                                            <th class="text-center">Publicadas</th>
                                            <th class="text-center">Revisadas</th>
                                            <th class="text-center">Rascunhos</th>
                                            <th class="text-center">Arquivadas</th>
                                            <th class="text-center">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($row['subjects'] as $subject)
                                            <tr class="{{ $subject['total'] === 0 ? 'table-danger' : '' }}">
                                                <td>{{ $subject['name'] }}</td>
                                                <td class="text-center"><strong>{{ $subject['available'] }}</strong></td>
                                                <td class="text-center">{{ $subject['published'] }}</td>
                                                <td class="text-center">{{ $subject['reviewed'] }}</td>
                                                <td class="text-center">{{ $subject['draft'] }}</td>
                                                <td class="text-center">{{ $subject['archived'] }}</td>
                                                <td class="text-center">{{ $subject['total'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="text-center text-muted">Nenhuma disciplina ativa configurada neste curso.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Nenhum curso encontrado para os filtros informados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($courseWithMostQuestions)
<div class="row">
    <div class="col-md-6">
        <div class="callout callout-success">
            <h5>Maior estoque disponível</h5>
            <p class="mb-0"><strong>{{ $courseWithMostQuestions['course']->title }}</strong>: {{ number_format($courseWithMostQuestions['available'], 0, ',', '.') }} questões.</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="callout callout-warning">
            <h5>Menor estoque disponível</h5>
            <p class="mb-0"><strong>{{ $courseWithLeastQuestions['course']->title }}</strong>: {{ number_format($courseWithLeastQuestions['available'], 0, ',', '.') }} questões.</p>
        </div>
    </div>
</div>
@endif
@endsection
