@extends('layouts.admin')

@section('title', 'Editar concurso')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Editar concurso</h1>
            <p class="text-muted mb-0">Atualize o concurso e os tópicos cobrados por disciplina.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-primary">Visualizar</a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    @include('admin.exams._form')
@endsection
