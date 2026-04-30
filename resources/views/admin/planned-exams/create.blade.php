@extends('admin.layout')

@section('title', 'Novo concurso')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h1 class="h3 mb-0">Novo concurso</h1>
        <p class="text-muted mb-0">Cadastre um concurso previsto ou publicado e vincule as disciplinas cobradas.</p>
    </div>

    <form method="POST" action="{{ route('admin.planned-exams.store') }}">
        @include('admin.planned-exams._form')
    </form>
</div>
@endsection
