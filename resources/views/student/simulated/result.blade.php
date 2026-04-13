@extends('layouts.student')

@section('title', 'Resultado do simulado')

@section('content')
    <style>
        .result-shell { display: grid; gap: 1rem; }
        .result-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
        }
        .result-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }
        .stat-box {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 20px;
        }
        .stat-label {
            color: #6b7280;
            font-size: .95rem;
        }
        .stat-value {
            font-size: 1.9rem;
            font-weight: 800;
            margin-top: 8px;
            color: #111827;
        }
        .summary-table thead th {
            color: #6b7280;
            font-weight: 600;
            border-bottom-width: 1px;
        }
        .result-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
        }
        .badge-correct {
            background: #dcfce7;
            color: #166534;
        }
        .badge-wrong {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-pending {
            background: #e5e7eb;
            color: #374151;
        }
        .review-link {
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 992px) {
            .result-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .result-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="result-shell">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h1 class="page-title">Resultado do simulado</h1>
                <p class="page-subtitle">{{ $simulatedExam->title }}</p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('student.simulated.index') }}" class="btn btn-outline-secondary">Voltar aos simulados</a>
                <a href="{{ route('student.simulated.show', $simulatedExam) }}" class="btn btn-primary">Revisar simulado</a>
            </div>
        </div>

        <div class="result-stats">
            <div class="stat-box">
                <div class="stat-label">Questões</div>
                <div class="stat-value">{{ $simulatedExam->total_questions }}</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Acertos</div>
                <div class="stat-value">{{ $simulatedExam->correct_answers }}</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Erros</div>
                <div class="stat-value">{{ max(0, $simulatedExam->total_questions - $simulatedExam->correct_answers) }}</div>
            </div>

            <div class="stat-box">
                <div class="stat-label">Acurácia</div>
                <div class="stat-value">{{ number_format((float) $simulatedExam->accuracy, 2, ',', '.') }}%</div>
            </div>
        </div>

        <div class="result-card">
            <div class="section-title">Resumo por questão</div>

            <div class="table-responsive">
                <table class="table align-middle summary-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Disciplina</th>
                            <th>Assunto</th>
                            <th>Concurso</th>
                            <th>Resposta</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="fw-semibold">{{ $item->position }}</td>
                                <td>{{ $item->question->subject->name ?? '-' }}</td>
                                <td>{{ $item->question->topic->name ?? '-' }}</td>
                                <td>{{ $item->question->exam->title ?? '-' }}</td>
                                <td>
                                    @if(is_null($item->answered_at))
                                        <span class="result-badge badge-pending">Pendente</span>
                                    @elseif($item->is_correct)
                                        <span class="result-badge badge-correct">Correta</span>
                                    @else
                                        <span class="result-badge badge-wrong">Errada</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a
                                        href="{{ route('student.simulated.show', ['simulatedExam' => $simulatedExam->id, 'question' => $item->position]) }}"
                                        class="review-link"
                                    >
                                        Revisar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
