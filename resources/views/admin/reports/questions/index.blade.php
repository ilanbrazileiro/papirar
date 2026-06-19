@extends('layouts.admin')

@section('title', 'Relatórios de Questões')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-7">
                <h1 class="m-0">Relatórios de Questões</h1>
                <p class="text-muted mb-0">Quantidade de questões por curso, disciplina, tópico e status editorial.</p>
            </div>
            <div class="col-sm-5 text-sm-right mt-3 mt-sm-0">
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list mr-1"></i> Ver questões
                </a>
                <a href="{{ route('admin.questions.import.create') }}" class="btn btn-primary">
                    <i class="fas fa-file-import mr-1"></i> Importar questões
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
            </div>
            <form method="GET" action="{{ route('admin.reports.questions.index') }}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="course_id">Curso</label>
                            <select name="course_id" id="course_id" class="form-control">
                                <option value="">Todos os cursos / base geral</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" @selected((string) $filters['course_id'] === (string) $course->id)>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Ao selecionar um curso, o relatório considera o escopo do curso.</small>
                        </div>

                        <div class="col-md-3">
                            <label for="subject_id">Disciplina</label>
                            <select name="subject_id" id="subject_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((string) $filters['subject_id'] === (string) $subject->id)>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="topic_id">Tópico</label>
                            <select name="topic_id" id="topic_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach($topics as $topic)
                                    <option value="{{ $topic->id }}" @selected((string) $filters['topic_id'] === (string) $topic->id)>
                                        {{ $topic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="draft" @selected($filters['status'] === 'draft')>Rascunho</option>
                                <option value="published" @selected($filters['status'] === 'published')>Publicada</option>
                                <option value="reviewed" @selected($filters['status'] === 'reviewed')>Revisada</option>
                                <option value="archived" @selected($filters['status'] === 'archived')>Arquivada</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.reports.questions.index') }}" class="btn btn-outline-secondary">Limpar filtros</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Aplicar filtros</button>
                </div>
            </form>
        </div>

        @if($selectedCourse)
            <div class="alert alert-info">
                <strong>Curso selecionado:</strong> {{ $selectedCourse->title }}.
                O relatório usa corporação, concurso, disciplinas e tópicos vinculados ao curso.
            </div>
        @endif

        <div class="row">
            @foreach($statusCards as $card)
                <div class="col-lg col-md-4 col-sm-6">
                    <div class="small-box bg-{{ $card['class'] }}">
                        <div class="inner">
                            <h3>{{ number_format($card['value'], 0, ',', '.') }}</h3>
                            <p>{{ $card['label'] }}</p>
                            <small>{{ $card['hint'] }}</small>
                        </div>
                        <div class="icon"><i class="{{ $card['icon'] }}"></i></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Publicadas pendentes de revisão</span>
                        <span class="info-box-number">{{ number_format($pendingReviewCount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Revisadas visíveis ao aluno</span>
                        <span class="info-box-number">{{ number_format($reviewedCount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Progresso editorial</span>
                        <span class="info-box-number">{{ number_format($reviewProgress, 1, ',', '.') }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Resumo por curso</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Visíveis</th>
                            <th class="text-center">Publicadas</th>
                            <th class="text-center">Revisadas</th>
                            <th class="text-center">Rascunhos</th>
                            <th class="text-center">Arquivadas</th>
                            <th class="text-center">Revisão</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courseRows as $row)
                            <tr>
                                <td><strong>{{ $row->title }}</strong></td>
                                <td class="text-center">
                                    @if($row->active)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($row->total, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($row->visible_total, 0, ',', '.') }}</td>
                                <td class="text-center text-warning">{{ number_format($row->published_total, 0, ',', '.') }}</td>
                                <td class="text-center text-success">{{ number_format($row->reviewed_total, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($row->draft_total, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($row->archived_total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $row->review_progress >= 80 ? 'success' : ($row->review_progress >= 40 ? 'warning' : 'danger') }}">
                                        {{ number_format($row->review_progress, 1, ',', '.') }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Nenhum curso encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title">Questões por disciplina</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Disciplina</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Publicadas</th>
                                    <th class="text-center">Revisadas</th>
                                    <th class="text-center">Rascunhos</th>
                                    <th class="text-center">Arquivadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bySubject as $row)
                                    <tr>
                                        <td>{{ $row->subject_name }}</td>
                                        <td class="text-center">{{ number_format($row->total, 0, ',', '.') }}</td>
                                        <td class="text-center text-warning">{{ number_format($row->published_total, 0, ',', '.') }}</td>
                                        <td class="text-center text-success">{{ number_format($row->reviewed_total, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ number_format($row->draft_total, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ number_format($row->archived_total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma questão encontrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Questões por dificuldade</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Dificuldade</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byDifficulty as $row)
                                    <tr>
                                        <td>
                                            @if($row->difficulty === 'easy')
                                                Fácil
                                            @elseif($row->difficulty === 'medium')
                                                Média
                                            @elseif($row->difficulty === 'hard')
                                                Difícil
                                            @else
                                                {{ $row->difficulty ?: 'Não informada' }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ number_format($row->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-4">Nenhuma questão encontrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-light border">
                    <strong>Leitura do relatório:</strong><br>
                    <span class="text-warning">Publicadas</span> são questões liberadas ao aluno e ainda pendentes de validação editorial.
                    <span class="text-success">Revisadas</span> são questões liberadas e já conferidas.
                </div>
            </div>
        </div>

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Questões por tópico</h3>
                <div class="card-tools"><span class="badge badge-light">Top 100 pelo filtro atual</span></div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Disciplina</th>
                            <th>Tópico</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Publicadas</th>
                            <th class="text-center">Revisadas</th>
                            <th class="text-center">Rascunhos</th>
                            <th class="text-center">Arquivadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byTopic as $row)
                            <tr>
                                <td>{{ $row->subject_name }}</td>
                                <td>{{ $row->topic_name }}</td>
                                <td class="text-center">{{ number_format($row->total, 0, ',', '.') }}</td>
                                <td class="text-center text-warning">{{ number_format($row->published_total, 0, ',', '.') }}</td>
                                <td class="text-center text-success">{{ number_format($row->reviewed_total, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($row->draft_total, 0, ',', '.') }}</td>
                                <td class="text-center">{{ number_format($row->archived_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhum tópico encontrado para os filtros atuais.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection
