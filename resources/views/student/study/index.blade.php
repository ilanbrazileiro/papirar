@extends('layouts.student')

@section('title', 'Iniciar estudo')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Iniciar estudo</h1>
        <p class="page-subtitle">Monte uma sessão com filtros salvos automaticamente para a próxima vez.</p>
    </div>

    <div class="card-soft p-4 p-md-5">
        <form method="POST" action="{{ route('student.study.start') }}">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Corporação</label>
                    <select name="corporation_id" class="form-select form-select-lg">
                        <option value="">Todas</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected(old('corporation_id', $savedFilter->corporation_id) == $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modo</label>
                    <select name="mode" class="form-select form-select-lg" required>
                        <option value="train" @selected(old('mode', $savedFilter->mode) === 'train')>Treino</option>
                        <option value="exam" @selected(old('mode', $savedFilter->mode) === 'exam')>Simulado rápido</option>
                        <option value="review" @selected(old('mode', $savedFilter->mode) === 'review')>Revisão de erros</option>
                    </select>
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
                    <label class="form-label fw-semibold">Quantidade</label>
                    <input type="number" name="quantity" min="1" max="200" class="form-control" value="{{ old('quantity', $savedFilter->quantity ?? 10) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dificuldade</label>
                    <select name="difficulty" class="form-select">
                        <option value="">Todas</option>
                        <option value="easy" @selected(old('difficulty', $savedFilter->difficulty) === 'easy')>Fácil</option>
                        <option value="medium" @selected(old('difficulty', $savedFilter->difficulty) === 'medium')>Média</option>
                        <option value="hard" @selected(old('difficulty', $savedFilter->difficulty) === 'hard')>Difícil</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Origem</label>
                    <select name="source_type" class="form-select">
                        <option value="">Todas</option>
                        <option value="official_exam" @selected(old('source_type', $savedFilter->source_type) === 'official_exam')>Prova oficial</option>
                        <option value="authored" @selected(old('source_type', $savedFilter->source_type) === 'authored')>Autoral</option>
                        <option value="adapted" @selected(old('source_type', $savedFilter->source_type) === 'adapted')>Adaptada</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-5">
                <button type="submit" class="btn btn-primary btn-lg px-4">Iniciar sessão</button>
            </div>
        </form>
    </div>
@endsection
