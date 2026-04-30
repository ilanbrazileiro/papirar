@extends('admin.layout')

@section('title', 'Editar concurso')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h1 class="h3 mb-0">Editar concurso</h1>
        <p class="text-muted mb-0">Atualize dados do concurso e suas disciplinas cobradas.</p>
    </div>

    <form method="POST" action="{{ route('admin.planned-exams.update', $exam) }}">
        @method('PUT')
        @include('admin.planned-exams._form')
    </form>
</div>
@endsection
