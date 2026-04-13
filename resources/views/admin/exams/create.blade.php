@extends('admin.layout')

@section('title', 'Novo concurso')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Novo concurso</h1>
        <p class="page-subtitle">Cadastre um concurso ligado a uma corporação.</p>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.exams.store') }}">
            @php($submitLabel = 'Salvar concurso')
            @include('admin.exams._form')
        </form>
    </div>
@endsection
