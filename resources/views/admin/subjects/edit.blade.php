@extends('admin.layout')

@section('title', 'Editar disciplina')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Editar disciplina</h1>
            <p class="page-subtitle">Atualize os dados da disciplina sem afetar os assuntos já cadastrados.</p>
        </div>
        <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline-secondary">Ver detalhes</a>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
            @method('PUT')
            @php($submitLabel = 'Salvar alterações')
            @include('admin.subjects._form')
        </form>
    </div>
@endsection
