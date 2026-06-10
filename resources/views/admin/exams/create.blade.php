@extends('layouts.admin')

@section('title', 'Novo concurso')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Novo concurso</h1>
            <p class="text-muted mb-0">Cadastre o concurso e defina as disciplinas e tópicos cobrados.</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    @include('admin.exams._form')
@endsection
