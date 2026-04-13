@extends('admin.layout')

@section('title', 'Nova corporação')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Nova corporação</h1>
        <p class="page-subtitle">Cadastre uma nova corporação para uso no sistema.</p>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.corporations.store') }}">
            @php($submitLabel = 'Salvar corporação')
            @include('admin.corporations._form')
        </form>
    </div>
@endsection
