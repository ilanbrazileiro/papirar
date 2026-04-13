@extends('layouts.student')

@section('title', 'Simulados')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Simulados</h1>
        <p class="page-subtitle">Monte simulados com filtros e acompanhe o seu histórico.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card-soft p-4">
                <div class="section-title">Criar simulado</div>

                <form method="POST" action="{{ route('student.simulated.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Ex.: Simulado PMERJ - Constitucional">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Corporação</label>
                            <select name="corporation_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach($corporations as $corporation)
                                    <option value="{{ $corporation->id }}" @selected(old('corporation_id', $savedFilter->corporation_id ?? null) == $corporation->id)>
                                        {{ $corporation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Disciplina</label>
                            <select name="subject_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(old('subject_id', $savedFilter->subject_id ?? null) == $subject->id)>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Assunto</label>
                            <select name="topic_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($topics as $topic)
                                    <option value="{{ $topic->id }}" @selected(old('topic_id', $savedFilter->topic_id ?? null) == $topic->id)>
                                        {{ $topic->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Dificuldade</label>
                            <select name="difficulty" class="form-select">
                                <option value="">Todas</option>
                                <option value="easy" @selected(old('difficulty', $savedFilter->difficulty ?? null) === 'easy')>Fácil</option>
                                <option value="medium" @selected(old('difficulty', $savedFilter->difficulty ?? null) === 'medium')>Média</option>
                                <option value="hard" @selected(old('difficulty', $savedFilter->difficulty ?? null) === 'hard')>Difícil</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Origem</label>
                            <select name="source_type" class="form-select">
                                <option value="">Todas</option>
                                <option value="official_exam" @selected(old('source_type', $savedFilter->source_type ?? null) === 'official_exam')>Prova oficial</option>
                                <option value="authored" @selected(old('source_type', $savedFilter->source_type ?? null) === 'authored')>Autoral</option>
                                <option value="adapted" @selected(old('source_type', $savedFilter->source_type ?? null) === 'adapted')>Adaptada</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantity" min="1" max="200" class="form-control" value="{{ old('quantity', $savedFilter->quantity ?? 20) }}" required>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-4">Gerar simulado</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-soft p-4">
                <div class="section-title">Histórico</div>

                @if($simulatedExams->count())
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Questões</th>
                                    <th>Acurácia</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($simulatedExams as $exam)
                                    <tr>
                                        <td class="fw-semibold">{{ $exam->title }}</td>
                                        <td>{{ $exam->total_questions }}</td>
                                        <td>{{ number_format((float) $exam->accuracy, 2, ',', '.') }}%</td>
                                        <td>{{ $exam->finished_at ? 'Finalizado' : 'Em andamento' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('student.simulated.show', $exam) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $simulatedExams->links() }}
                    </div>
                @else
                    <div class="small-muted">Nenhum simulado criado.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
