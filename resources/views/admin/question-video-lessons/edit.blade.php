@extends('layouts.admin')

@section('title', 'Editar aula por questão')

@section('content')
    <h1 class="h3 mb-3">Editar aula por questão</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.question-video-lessons.update', $lesson) }}">
        @method('PUT')
        @include('admin.question-video-lessons._form')
    </form>
@endsection
