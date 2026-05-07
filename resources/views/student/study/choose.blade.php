@extends('layouts.student')

@section('title', 'Escolher forma de estudo')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Como você quer estudar?</h1>
        <p class="page-subtitle">
            Escolha o estudo direcionado por concurso previsto/publicado ou monte seu próprio filtro livre de questões.
        </p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-soft p-4 h-100 border border-primary-subtle">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <span class="badge bg-primary mb-2">Recomendado</span>
                        <h2 class="h4 mb-2">Estudar por concurso</h2>
                        <p class="text-muted mb-0">
                            Escolha a corporação e o concurso que pretende fazer. O Papirar carrega as disciplinas vinculadas ao edital/planejamento e você decide quais quer treinar.
                        </p>
                    </div>
                </div>

                <ul class="text-muted small mb-4 ps-3">
                    <li>Ideal para CHOE, CHOAE e outros concursos internos.</li>
                    <li>Reaproveita questões por disciplina e tópico.</li>
                    <li>Evita que o aluno monte filtros errados manualmente.</li>
                </ul>

                <a href="{{ route('student.exam-study.create') }}" class="btn btn-primary btn-lg w-100">
                    Estudar por concurso
                </a>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-soft p-4 h-100">
                <div class="mb-3">
                    <span class="badge bg-secondary mb-2">Avançado</span>
                    <h2 class="h4 mb-2">Filtro livre de questões</h2>
                    <p class="text-muted mb-0">
                        Monte manualmente uma sessão escolhendo corporação, disciplina, assunto, dificuldade e origem da questão.
                    </p>
                </div>

                <ul class="text-muted small mb-4 ps-3">
                    <li>Bom para revisar uma disciplina específica.</li>
                    <li>Permite estudar fora de um concurso previsto.</li>
                    <li>Útil para usuários mais experientes.</li>
                </ul>

                <a href="{{ route('student.study.filter') }}" class="btn btn-outline-primary btn-lg w-100">
                    Usar filtro livre
                </a>
            </div>
        </div>
    </div>
@endsection
