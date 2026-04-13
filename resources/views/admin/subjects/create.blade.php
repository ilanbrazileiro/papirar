@extends('admin.layout')

@section('title', 'Nova disciplina')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Nova disciplina</h1>
        <p class="page-subtitle">Cadastre uma nova disciplina para uso no sistema.</p>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @php($submitLabel = 'Salvar disciplina')
            @include('admin.subjects._form')
        </form>
    </div>
@endsection
