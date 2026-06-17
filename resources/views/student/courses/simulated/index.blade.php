@extends('layouts.student')

@section('title', 'Simulados - ' . $course->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Simulados</h1>
            <p class="page-subtitle">{{ $course->title }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.show', $course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar questões</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card-soft p-4">
                <div class="section-title">Criar simulado livre</div>
                @if($subjects->isEmpty())
                    <div class="alert alert-warning mb-0">Este curso ainda não possui disciplinas disponíveis para simulado.</div>
                @else
                    <form method="POST" action="{{ route('student.course.simulated.store', $course) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Título do simulado</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Ex.: Simulado 01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Disciplinas</label>
                            <div class="border rounded p-3 bg-white" style="max-height: 260px; overflow:auto;">
                                @foreach($subjects as $subject)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" id="sim-subject-{{ $subject->id }}" @checked(in_array((int) $subject->id, array_map('intval', old('subject_ids', [])), true))>
                                        <label class="form-check-label" for="sim-subject-{{ $subject->id }}">{{ $subject->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('subject_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dificuldade</label>
                            <select name="difficulty" class="form-control">
                                <option value="">Todas</option>
                                <option value="easy" @selected(old('difficulty') === 'easy')>Fácil</option>
                                <option value="medium" @selected(old('difficulty') === 'medium')>Média</option>
                                <option value="hard" @selected(old('difficulty') === 'hard')>Difícil</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantidade</label>
                                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 20) }}" min="1" max="120">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempo em minutos</label>
                                <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 60) }}" min="5" max="300">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar simulado</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card-soft p-4">
                <div class="section-title">Meus simulados deste curso</div>
                @if($simulatedExams->isEmpty())
                    <div class="small-muted">Você ainda não iniciou simulados neste curso.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Simulado</th><th>Questões</th><th>Acerto</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @foreach($simulatedExams as $simulatedExam)
                                    <tr>
                                        <td><strong>{{ $simulatedExam->title }}</strong><div class="small-muted">{{ optional($simulatedExam->started_at)->format('d/m/Y H:i') }}</div></td>
                                        <td>{{ $simulatedExam->total_questions }}</td>
                                        <td>{{ number_format((float) $simulatedExam->accuracy, 2, ',', '.') }}%</td>
                                        <td>@if($simulatedExam->finished_at)<span class="badge bg-success">Finalizado</span>@else<span class="badge bg-warning text-dark">Em andamento</span>@endif</td>
                                        <td class="text-end">
                                            @if($simulatedExam->finished_at)
                                                <a href="{{ route('student.course.simulated.result', $simulatedExam) }}" class="btn btn-sm btn-outline-primary">Resultado</a>
                                            @else
                                                <a href="{{ route('student.course.simulated.show', $simulatedExam) }}" class="btn btn-sm btn-primary">Continuar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $simulatedExams->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
