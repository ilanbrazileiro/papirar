@extends('layouts.admin')

@section('title', 'Nova aula por questão')

@section('content')
    <h1 class="h3 mb-3">Nova aula por questão</h1>

    <form method="POST" action="{{ route('admin.question-video-lessons.store') }}">
        @include('admin.question-video-lessons._form')
    </form>
@endsection
