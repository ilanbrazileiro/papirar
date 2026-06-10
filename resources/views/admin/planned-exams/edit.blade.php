@extends('layouts.admin')

@section('title', 'Editar concurso')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Editar concurso</h1>
            <p class="text-muted mb-0">Atualize os dados do concurso, disciplinas e tópicos cobrados.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.planned-exams.update', $exam) }}">
        @method('PUT')
        @include('admin.planned-exams._form')
    </form>
@endsection
