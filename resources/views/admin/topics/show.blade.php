@extends('admin.layout')

@section('title', 'Detalhes do assunto')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $topic->name }}</h1>
            <p class="page-subtitle">Detalhes completos do assunto cadastrado.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.topics.edit', $topic) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.topics.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="panel p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="text-muted mb-1">Disciplina</div>
                <div class="fw-semibold">{{ $topic->subject->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Status</div>
                <div>
                    @if($topic->active)
                        <span class="badge text-bg-success">Ativo</span>
                    @else
                        <span class="badge text-bg-secondary">Inativo</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Nome</div>
                <div class="fw-semibold fs-5">{{ $topic->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Slug</div>
                <div>{{ $topic->slug }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted mb-1">Descrição</div>
                <div>{{ $topic->description ?: 'Sem descrição cadastrada.' }}</div>
            </div>
        </div>
    </div>
@endsection
