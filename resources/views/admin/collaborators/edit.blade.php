@extends('layouts.admin')

@section('title', 'Editar colaborador | Papirar')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Editar colaborador</h1>
    <p class="text-muted mb-0">Atualize dados, perfil, status ou senha do colaborador.</p>
</div>

<form method="POST" action="{{ route('admin.collaborators.update', $collaborator) }}">
    @method('PUT')
    @include('admin.collaborators._form')
</form>

@if($collaborator->id !== auth()->id())
    <form id="delete-collaborator-form" method="POST" action="{{ route('admin.collaborators.destroy', $collaborator) }}" class="d-none" onsubmit="return confirm('Tem certeza que deseja remover este colaborador?');">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
