@extends('admin.layout')

@section('title', 'Editar questão')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Editar questão #{{ $question->id }}</h1>
            <p class="page-subtitle">Atualize os dados mantendo os vínculos e as alternativas consistentes.</p>
        </div>
        <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-outline-secondary">Ver detalhes</a>
    </div>

    <form method="POST" action="{{ route('admin.questions.update', $question) }}">
        @method('PUT')
        <div class="panel p-4 p-md-5">
            @php($submitLabel = 'Salvar alterações')
            @include('admin.questions._form')
        </div>
    </form>
@endsection
