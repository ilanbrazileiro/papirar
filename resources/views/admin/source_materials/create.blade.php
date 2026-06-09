@extends('layouts.admin')

@section('title', 'Nova fonte/material')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Nova fonte/material</h1>
        <a href="{{ route('admin.source-materials.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.source-materials.store') }}">
                @include('admin.source_materials._form')
            </form>
        </div>
    </div>
</div>
@endsection
