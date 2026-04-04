@extends('layouts.student')

@section('title', 'Iniciar estudo')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Iniciar estudo</h1>
        <p class="page-subtitle">
            Escolha os filtros e inicie uma nova sessão.
        </p>
    </div>

    <div class="card-soft p-4 p-md-5">
        <form method="POST" action="{{ route('study.start') }}">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Corporação</label>
                    <select name="corporation_id" class="form-select form-select-lg" required>
                        <option value="">Selecione</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected(old('corporation_id') == $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modo de estudo</label>
                    <select name="mode" class="form-select form-select-lg" required>
                        <option value="train" @selected(old('mode') === 'train')>Treino</option>
                        <option value="exam" @selected(old('mode') === 'exam')>Simulado</option>
                        <option value="review" @selected(old('mode') === 'review')>Revisão de erros</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Disciplina</label>
                    <select name="subject_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Assunto</label>
                    <select name="topic_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}" @selected(old('topic_id') == $topic->id)>
                                {{ $topic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Dificuldade</label>
                    <select name="difficulty" class="form-select">
                        <option value="">Todas</option>
                        <option value="easy" @selected(old('difficulty') === 'easy')>Fácil</option>
                        <option value="medium" @selected(old('difficulty') === 'medium')>Média</option>
                        <option value="hard" @selected(old('difficulty') === 'hard')>Difícil</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Origem</label>
                    <select name="source_type" class="form-select">
                        <option value="">Todas</option>
                        <option value="official_exam" @selected(old('source_type') === 'official_exam')>Prova oficial</option>
                        <option value="authored" @selected(old('source_type') === 'authored')>Autoral</option>
                        <option value="adapted" @selected(old('source_type') === 'adapted')>Adaptada</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Quantidade de questões</label>
                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="100"
                        value="{{ old('quantity', 10) }}"
                        class="form-control"
                        required
                    >
                </div>
            </div>

            <div class="d-flex justify-content-end mt-5">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    Iniciar estudo
                </button>
            </div>
        </form>
    </div>
@endsection
