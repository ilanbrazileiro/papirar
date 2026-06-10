@extends('layouts.admin')

@section('title', 'Novo concurso')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Novo concurso</h1>
            <p class="text-muted mb-0">Cadastre um concurso previsto ou publicado e vincule as disciplinas e tópicos cobrados.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.planned-exams.store') }}">
        @include('admin.planned-exams._form')
    </form>
@endsection
