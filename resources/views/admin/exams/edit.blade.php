@extends('admin.layout')

@section('title', 'Editar concurso previsto')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-3">Editar concurso previsto</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.planned-exams.update', $exam) }}">
        @method('PUT')
        @include('admin.exams._form')
    </form>
</div>
@endsection
