@extends('layouts.admin')

@section('title', 'Dashboard de Conteúdo')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard de Conteúdo</h1>
            <p class="text-muted mb-0">Acompanhe o volume de questões e a cobertura dos concursos previstos.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">Nova questão</a>
            <a href="{{ route('admin.questions.import.create') }}" class="btn btn-outline-primary">Importar questões</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">Total de questões</div>
                    <div class="display-5 fw-bold mb-1">{{ $stats['questions_total'] ?? 0 }}</div>
                    <div class="small text-muted">
                        {{ $stats['questions_published'] ?? 0 }} publicadas · {{ $stats['questions_draft'] ?? 0 }} rascunhos
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-outline-secondary">Ver questões</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-1">Concursos previstos</h2>
            <p class="text-muted mb-0">Cada card mostra a quantidade de questões disponíveis conforme disciplinas e tópicos vinculados ao concurso.</p>
        </div>
        <a href="{{ route('admin.planned-exams.create') }}" class="btn btn-sm btn-outline-primary">Novo concurso previsto</a>
    </div>

    @if($plannedExams->count())
        <div class="row g-3">
            @foreach($plannedExams as $exam)
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <div class="text-muted small">{{ $exam->corporation_name ?? 'Sem corporação' }}</div>
                                    <h3 class="h5 mb-1">{{ $exam->title }}</h3>
                                    <div class="small text-muted">
                                        {{ $exam->year ?? '-' }} · {{ strtoupper($exam->exam_type ?? 'concurso') }}
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark">Previsto</span>
                            </div>

                            <div class="my-3">
                                <div class="display-6 fw-bold">{{ $exam->questions_count ?? 0 }}</div>
                                <div class="text-muted small">questões disponíveis para este concurso</div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="fw-semibold">{{ $exam->subjects_count ?? 0 }}</div>
                                        <div class="small text-muted">disciplinas</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="fw-semibold">{{ $exam->topics_count ?? 0 }}</div>
                                        <div class="small text-muted">tópicos</div>
                                    </div>
                                </div>
                            </div>

                            @if(($exam->questions_count ?? 0) === 0)
                                <div class="alert alert-warning small py-2 mb-3">
                                    Ainda não há questões publicadas compatíveis com as disciplinas e tópicos deste concurso.
                                </div>
                            @endif

                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('admin.planned-exams.edit', $exam->id) }}" class="btn btn-sm btn-outline-primary">Editar concurso</a>
                                <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-outline-secondary">Ver questões</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <h3 class="h5">Nenhum concurso previsto cadastrado</h3>
                <p class="text-muted mb-3">Quando um concurso previsto for criado, ele aparecerá automaticamente nesta dashboard.</p>
                <a href="{{ route('admin.planned-exams.create') }}" class="btn btn-primary">Cadastrar concurso previsto</a>
            </div>
        </div>
    @endif
</div>
@endsection
