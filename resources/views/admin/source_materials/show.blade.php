@extends('layouts.admin')

@section('title', 'Fonte/material')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Fonte/material</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.source-materials.edit', $material) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.source-materials.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">ID</dt><dd class="col-sm-9">{{ $material->id }}</dd>
                <dt class="col-sm-3">Título</dt><dd class="col-sm-9">{{ $material->title }}</dd>
                <dt class="col-sm-3">Slug</dt><dd class="col-sm-9">{{ $material->slug }}</dd>
                <dt class="col-sm-3">Corporação</dt><dd class="col-sm-9">{{ $material->corporation->name ?? 'Geral' }}</dd>
                <dt class="col-sm-3">Disciplina</dt><dd class="col-sm-9">{{ $material->subject->name ?? '-' }}</dd>
                <dt class="col-sm-3">Tipo</dt><dd class="col-sm-9">{{ $material->material_type }}</dd>
                <dt class="col-sm-3">Ano</dt><dd class="col-sm-9">{{ $material->year ?? '-' }}</dd>
                <dt class="col-sm-3">Código/referência</dt><dd class="col-sm-9">{{ $material->reference_code ?? '-' }}</dd>
                <dt class="col-sm-3">URL</dt><dd class="col-sm-9">@if($material->url)<a href="{{ $material->url }}" target="_blank" rel="noopener">{{ $material->url }}</a>@else - @endif</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $material->active ? 'Ativo' : 'Inativo' }}</dd>
                <dt class="col-sm-3">Descrição</dt><dd class="col-sm-9">{!! nl2br(e($material->description ?? '-')) !!}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
