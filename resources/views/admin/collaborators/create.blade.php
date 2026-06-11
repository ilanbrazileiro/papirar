@extends('layouts.admin')

@section('title', 'Novo colaborador | Papirar')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Novo colaborador</h1>
    <p class="text-muted mb-0">Cadastre um usuário com acesso administrativo ao Papirar.</p>
</div>

<form method="POST" action="{{ route('admin.collaborators.store') }}">
    @include('admin.collaborators._form')
</form>
@endsection
