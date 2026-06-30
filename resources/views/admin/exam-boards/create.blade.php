@extends('layouts.admin')

@section('title', 'Nova banca | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Nova banca</h1>
            <p class="text-muted mb-0">Cadastre uma banca organizadora para vincular às questões.</p>
        </div>
        <a href="{{ route('admin.exam-boards.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    @include('admin.exam-boards._form', [
        'formAction' => route('admin.exam-boards.store'),
        'formMethod' => 'POST',
        'submitLabel' => 'Cadastrar banca',
    ])
</div>
@endsection
