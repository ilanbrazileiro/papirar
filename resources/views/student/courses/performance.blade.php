@extends('layouts.student')

@section('title', 'Desempenho - ' . $course->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Desempenho</h1>
            <p class="page-subtitle mb-1">{{ $course->title }}</p>
            <div class="small-muted">Acompanhamento por curso com base nos estudos e simulados vinculados a este curso.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.show', $course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar</a>
            <a href="{{ route('student.courses.simulated.index', $course) }}" class="btn btn-outline-primary">Simulados</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Aproveitamento em treino</div>
                <div class="value">{{ number_format($trainingAccuracy, 1, ',', '.') }}%</div>
                <div class="small-muted">{{ $trainingCorrect }} certas de {{ $trainingAnswered }} respondidas</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Questões feitas</div>
                <div class="value">{{ $distinctAnsweredQuestions }}</div>
                <div class="small-muted">{{ $unansweredQuestions }} ainda não feitas</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Sessões de estudo</div>
                <div class="value">{{ (int) ($training->sessions ?? 0) }}</div>
                <div class="small-muted">Última: {{ $training->last_answered_at ? \Carbon\Carbon::parse($training->last_answered_at)->format('d/m/Y H:i') : '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Simulados finalizados</div>
                <div class="value">{{ $simulatedFinished }}</div>
                <div class="small-muted">Média: {{ number_format($simulatedAccuracy, 1, ',', '.') }}%</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="section-title mb-0">Desempenho por disciplina</div>
                        <div class="small-muted">Priorize disciplinas com maior volume e menor aproveitamento.</div>
                    </div>
                </div>

                @if($bySubject->isEmpty())
                    <p class="small-muted mb-0">Ainda não há respostas registradas neste curso.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Disciplina</th>
                                    <th class="text-center">Respondidas</th>
                                    <th class="text-center">Certas</th>
                                    <th class="text-center">Erros</th>
                                    <th class="text-end">Aproveitamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bySubject as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td class="text-center">{{ $row->answered }}</td>
                                        <td class="text-center">{{ $row->correct }}</td>
                                        <td class="text-center">{{ $row->wrong }}</td>
                                        <td class="text-end"><strong>{{ number_format($row->accuracy, 1, ',', '.') }}%</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 h-100">
                <div class="section-title">Resumo do curso</div>
                <ul class="list-clean mb-0">
                    <li class="py-2">Questões disponíveis: <strong>{{ $availableQuestions }}</strong></li>
                    <li class="py-2">Respondidas em treino: <strong>{{ $trainingAnswered }}</strong></li>
                    <li class="py-2">Acertos em treino: <strong>{{ $trainingCorrect }}</strong></li>
                    <li class="py-2">Erros em treino: <strong>{{ $trainingWrong }}</strong></li>
                    <li class="py-2">Simulados criados: <strong>{{ $simulatedTotal }}</strong></li>
                    <li class="py-2">Melhor simulado: <strong>{{ number_format((float) ($simulated->best_accuracy ?? 0), 1, ',', '.') }}%</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card-soft p-4 h-100">
                <div class="section-title">Tópicos para atenção</div>
                @if($byTopic->isEmpty())
                    <p class="small-muted mb-0">Ainda não há dados por tópico.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tópico</th>
                                    <th class="text-center">Resp.</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byTopic as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td class="text-center">{{ $row->answered }}</td>
                                        <td class="text-end"><strong>{{ number_format($row->accuracy, 1, ',', '.') }}%</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-soft p-4 h-100">
                <div class="section-title">Desempenho por dificuldade</div>
                @if($byDifficulty->isEmpty())
                    <p class="small-muted mb-0">Ainda não há dados por dificuldade.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Dificuldade</th>
                                    <th class="text-center">Respondidas</th>
                                    <th class="text-center">Certas</th>
                                    <th class="text-end">Aproveitamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byDifficulty as $row)
                                    @php
                                        $difficultyLabel = [
                                            'easy' => 'Fácil',
                                            'medium' => 'Média',
                                            'hard' => 'Difícil',
                                        ][$row->difficulty] ?? $row->difficulty;
                                    @endphp
                                    <tr>
                                        <td>{{ $difficultyLabel }}</td>
                                        <td class="text-center">{{ $row->answered }}</td>
                                        <td class="text-center">{{ $row->correct }}</td>
                                        <td class="text-end"><strong>{{ number_format($row->accuracy, 1, ',', '.') }}%</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card-soft p-4 h-100">
                <div class="section-title">Questões para revisar</div>
                @if($reviewQuestions->isEmpty())
                    <p class="small-muted mb-0">Nenhuma questão errada registrada neste curso.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Questão</th>
                                    <th>Disciplina</th>
                                    <th class="text-center">Erros</th>
                                    <th class="text-end">Último erro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviewQuestions as $row)
                                    <tr>
                                        <td>{{ $row->short_statement }}</td>
                                        <td>{{ $row->subject_name }}</td>
                                        <td class="text-center">{{ $row->wrong_count }}</td>
                                        <td class="text-end">{{ $row->last_wrong_at ? \Carbon\Carbon::parse($row->last_wrong_at)->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-soft p-4 h-100">
                <div class="section-title">Últimos simulados</div>
                @if($latestSimulated->isEmpty())
                    <p class="small-muted mb-0">Nenhum simulado criado neste curso.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Simulado</th>
                                    <th class="text-center">Questões</th>
                                    <th class="text-end">Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestSimulated as $sim)
                                    <tr>
                                        <td>{{ $sim->title ?: ('Simulado #' . $sim->id) }}</td>
                                        <td class="text-center">{{ $sim->total_questions }}</td>
                                        <td class="text-end">
                                            @if($sim->finished_at)
                                                {{ number_format((float) $sim->accuracy, 1, ',', '.') }}%
                                            @else
                                                <span class="small-muted">Em andamento</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card-soft p-4">
        <div class="section-title">Últimas respostas</div>
        @if($recentAnswers->isEmpty())
            <p class="small-muted mb-0">Ainda não há respostas registradas neste curso.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Resultado</th>
                            <th>Questão</th>
                            <th>Disciplina</th>
                            <th>Tópico</th>
                            <th class="text-end">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAnswers as $row)
                            <tr>
                                <td>
                                    @if($row->is_correct)
                                        <span class="badge bg-success">Certa</span>
                                    @else
                                        <span class="badge bg-danger">Errada</span>
                                    @endif
                                </td>
                                <td>{{ $row->short_statement }}</td>
                                <td>{{ $row->subject_name }}</td>
                                <td>{{ $row->topic_name ?: '-' }}</td>
                                <td class="text-end">{{ $row->answered_at ? \Carbon\Carbon::parse($row->answered_at)->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
