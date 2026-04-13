@extends('admin.layout')

@section('title', 'Editar corporação')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Editar corporação</h1>
            <p class="page-subtitle">Atualize os dados da corporação sem afetar as questões existentes.</p>
        </div>
        <a href="{{ route('admin.corporations.show', $corporation) }}" class="btn btn-outline-secondary">Ver detalhes</a>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.corporations.update', $corporation) }}">
            @method('PUT')
            @php($submitLabel = 'Salvar alterações')
            @include('admin.corporations._form')
        </form>
    </div>
@endsection
