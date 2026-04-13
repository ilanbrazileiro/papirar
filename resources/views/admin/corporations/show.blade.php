@extends('admin.layout')

@section('title', 'Detalhes da corporação')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $corporation->name }}</h1>
            <p class="page-subtitle">Dados completos da corporação cadastrada.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.corporations.edit', $corporation) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.corporations.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="panel p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="text-muted mb-1">Nome</div>
                <div class="fw-semibold fs-5">{{ $corporation->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Slug</div>
                <div class="fw-semibold">{{ $corporation->slug }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Status</div>
                <div>
                    @if($corporation->active)
                        <span class="badge text-bg-success">Ativa</span>
                    @else
                        <span class="badge text-bg-secondary">Inativa</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Criada em</div>
                <div>{{ optional($corporation->created_at)->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted mb-1">Descrição</div>
                <div>{{ $corporation->description ?: 'Sem descrição cadastrada.' }}</div>
            </div>
        </div>
    </div>
@endsection
