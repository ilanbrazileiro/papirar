@extends('layouts.student')

@section('title', 'Simulados')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Simulados</h1>
        <p class="page-subtitle">Monte um simulado por corporação, disciplina, assunto, fonte, dificuldade e origem.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    <div class="card-soft p-4 p-md-5 mb-4">
        <form method="POST" action="{{ route('student.simulated.store') }}">
            @csrf

            <div class="row g-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Título do simulado</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Opcional">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Corporação</label>
                    <select name="corporation_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected(old('corporation_id', $savedFilter->corporation_id) == $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Quantidade</label>
                    <input type="number" name="quantity" min="1" max="100" class="form-control" value="{{ old('quantity', $savedFilter->quantity ?? 20) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Disciplina</label>
                    <select name="subject_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id', $savedFilter->subject_id) == $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Assunto</label>
                    <select name="topic_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" @selected(old('topic_id', $savedFilter->topic_id) == $topic->id)>
                                {{ $topic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Dificuldade</label>
                    <select name="difficulty" class="form-select">
                        <option value="">Todas</option>
                        <option value="easy" @selected(old('difficulty', $savedFilter->difficulty) === 'easy')>Fácil</option>
                        <option value="medium" @selected(old('difficulty', $savedFilter->difficulty) === 'medium')>Média</option>
                        <option value="hard" @selected(old('difficulty', $savedFilter->difficulty) === 'hard')>Difícil</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fonte / Bibliografia</label>
                    <select name="source_material_id" class="form-select">
                        <option value="">Todas as fontes</option>
                        @foreach($sourceMaterials as $material)
                            <option value="{{ $material->id }}" @selected(old('source_material_id', $savedFilter->source_material_id) == $material->id)>
                                {{ $material->title }}
                                @if($material->subject)
                                    — {{ $material->subject->name }}
                                @endif
                                @if($material->corporation)
                                    — {{ $material->corporation->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Origem</label>
                    <select name="source_type" class="form-select">
                        <option value="">Todas</option>
                        <option value="exam" @selected(old('source_type', $savedFilter->source_type) === 'exam')>Prova oficial</option>
                        <option value="authored" @selected(old('source_type', $savedFilter->source_type) === 'authored')>Autoral</option>
                        <option value="adapted" @selected(old('source_type', $savedFilter->source_type) === 'adapted')>Adaptada</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-4">Gerar simulado</button>
            </div>
        </form>
    </div>

    <div class="card-soft p-4">
        <h2 class="h5 mb-3">Meus simulados</h2>

        @if($simulatedExams->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Questões</th>
                            <th>Acertos</th>
                            <th>Aproveitamento</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($simulatedExams as $exam)
                            <tr>
                                <td>{{ $exam->title }}</td>
                                <td>{{ $exam->total_questions }}</td>
                                <td>{{ $exam->correct_answers }}</td>
                                <td>{{ number_format((float) $exam->accuracy, 2, ',', '.') }}%</td>
                                <td>
                                    @if($exam->finished_at)
                                        <span class="badge bg-success">Finalizado</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Em andamento</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($exam->finished_at)
                                        <a href="{{ route('student.simulated.result', $exam) }}" class="btn btn-sm btn-outline-primary">Resultado</a>
                                    @else
                                        <a href="{{ route('student.simulated.show', $exam) }}" class="btn btn-sm btn-primary">Continuar</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $simulatedExams->links() }}
        @else
            <p class="text-muted mb-0">Você ainda não gerou nenhum simulado.</p>
        @endif
    </div>
@endsection
