@extends('admin.layout')

@section('title', 'Editar assunto')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Editar assunto</h1>
            <p class="page-subtitle">Atualize os dados sem perder o vínculo com a disciplina.</p>
        </div>
        <a href="{{ route('admin.topics.show', $topic) }}" class="btn btn-outline-secondary">Ver detalhes</a>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.topics.update', $topic) }}">
            @method('PUT')
            @php($submitLabel = 'Salvar alterações')
            @include('admin.topics._form')
        </form>
    </div>
@endsection
