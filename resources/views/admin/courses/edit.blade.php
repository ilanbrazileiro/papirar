@extends('layouts.admin')

@section('title', 'Editar curso | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Editar curso</h1>
            <p class="text-muted mb-0">{{ $course->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-outline-secondary">Ver</a>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    @include('admin.courses._form')
</div>
@endsection
