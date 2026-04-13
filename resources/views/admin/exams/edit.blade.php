@extends('admin.layout')

@section('title', 'Editar concurso')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Editar concurso</h1>
            <p class="page-subtitle">Atualize os dados do concurso sem perder o vínculo com a corporação.</p>
        </div>
        <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-secondary">Ver detalhes</a>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.exams.update', $exam) }}">
            @method('PUT')
            @php($submitLabel = 'Salvar alterações')
            @include('admin.exams._form')
        </form>
    </div>
@endsection
