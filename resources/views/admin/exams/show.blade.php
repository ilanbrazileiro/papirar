@extends('admin.layout')

@section('title', 'Detalhes do concurso')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $exam->title }}</h1>
            <p class="page-subtitle">Detalhes completos do concurso cadastrado.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="panel p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="text-muted mb-1">Corporação</div>
                <div class="fw-semibold">{{ $exam->corporation->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Status</div>
                <div>
                    @if($exam->active)
                        <span class="badge text-bg-success">Ativo</span>
                    @else
                        <span class="badge text-bg-secondary">Inativo</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Título</div>
                <div class="fw-semibold fs-5">{{ $exam->title }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted mb-1">Ano</div>
                <div>{{ $exam->year }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted mb-1">Tipo</div>
                <div>{{ $exam->exam_type }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted mb-1">Descrição</div>
                <div>{{ $exam->description ?: 'Sem descrição cadastrada.' }}</div>
            </div>
        </div>
    </div>
@endsection
