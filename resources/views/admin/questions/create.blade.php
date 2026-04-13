@extends('admin.layout')

@section('title', 'Nova questão')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Nova questão</h1>
        <p class="page-subtitle">Cadastre uma questão completa com alternativas, vínculos e gabarito comentado.</p>
    </div>

    <form method="POST" action="{{ route('admin.questions.store') }}">
        <div class="panel p-4 p-md-5">
            @php($submitLabel = 'Salvar questão')
            @include('admin.questions._form')
        </div>
    </form>
@endsection
