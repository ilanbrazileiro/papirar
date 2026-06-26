@extends('layouts.student')

@section('title', 'Simulados do curso')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Simulados</h1>
            <p class="page-subtitle">
                {{ $course->title }} · Monte simulados com as disciplinas disponíveis neste curso.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.show', $course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar por questões</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card-soft p-4">
                <div class="section-title">Criar novo simulado</div>
                <p class="small-muted">
                    Selecione as disciplinas, quantidade de questões, dificuldade e tempo de prova.
                </p>

                <form method="POST" action="{{ route('student.courses.simulated.store', $course) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título do simulado</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Ex.: Simulado CHOAE 01">
                        @error('title')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Disciplinas</label>

                        @if($subjects->isEmpty())
                            <div class="alert alert-warning rounded-4 mb-0">
                                Este curso ainda não possui disciplinas vinculadas para criação de simulados.
                            </div>
                        @else
                            <div class="border rounded-4 p-3 bg-white" style="max-height: 260px; overflow:auto;">
                                @foreach($subjects as $subject)
                                    <label class="d-flex align-items-center gap-2 py-2 mb-0">
                                        <input
                                            type="checkbox"
                                            name="subject_ids[]"
                                            value="{{ $subject->id }}"
                                            @checked(in_array($subject->id, old('subject_ids', [])))
                                        >
                                        <span>{{ $subject->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        @error('subject_ids')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('subject_ids.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Questões</label>
                            <input type="number" name="quantity" class="form-control" min="1" max="120" value="{{ old('quantity', 20) }}" required>
                            @error('quantity')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tempo</label>
                            <input type="number" name="duration_minutes" class="form-control" min="5" max="300" value="{{ old('duration_minutes', 60) }}" required>
                            <div class="small-muted mt-1">em minutos</div>
                            @error('duration_minutes')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Dificuldade</label>
                            <select name="difficulty" class="form-select">
                                <option value="">Todas</option>
                                <option value="easy" @selected(old('difficulty') === 'easy')>Fácil</option>
                                <option value="medium" @selected(old('difficulty') === 'medium')>Média</option>
                                <option value="hard" @selected(old('difficulty') === 'hard')>Difícil</option>
                            </select>
                            @error('difficulty')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button class="btn btn-primary" @disabled($subjects->isEmpty())>
                            Criar simulado
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-soft p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                    <div>
                        <div class="section-title mb-1">Simulados criados</div>
                        <div class="small-muted">Histórico dos simulados deste curso.</div>
                    </div>
                </div>

                @if($simulatedExams->isEmpty())
                    <div class="border rounded-4 p-4 bg-white">
                        <div class="fw-semibold mb-1">Nenhum simulado criado ainda.</div>
                        <div class="small-muted">Use o formulário ao lado para criar o primeiro simulado deste curso.</div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Questões</th>
                                    <th>Tempo</th>
                                    <th>Status</th>
                                    <th>Acerto</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($simulatedExams as $simulatedExam)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $simulatedExam->title }}</div>
                                            <div class="small-muted">
                                                Criado em {{ optional($simulatedExam->created_at)->format('d/m/Y H:i') }}
                                            </div>
                                        </td>
                                        <td>{{ $simulatedExam->total_questions }}</td>
                                        <td>{{ $simulatedExam->duration_minutes }} min</td>
                                        <td>
                                            @if($simulatedExam->finished_at)
                                                <span class="badge bg-success">Finalizado</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Em andamento</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format((float) $simulatedExam->accuracy, 2, ',', '.') }}%</td>
                                        <td class="text-end">
                                            @if($simulatedExam->finished_at)
                                                <a href="{{ route('student.courses.simulated.result', [$course, $simulatedExam]) }}" class="btn btn-sm btn-outline-primary">Resultado</a>
                                            @else
                                                <a href="{{ route('student.courses.simulated.show', [$course, $simulatedExam]) }}" class="btn btn-sm btn-primary">Continuar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $simulatedExams->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
