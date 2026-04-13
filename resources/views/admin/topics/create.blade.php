@extends('admin.layout')

@section('title', 'Novo assunto')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Novo assunto</h1>
        <p class="page-subtitle">Cadastre um assunto ligado a uma disciplina.</p>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.topics.store') }}">
            @php($submitLabel = 'Salvar assunto')
            @include('admin.topics._form')
        </form>
    </div>
@endsection
