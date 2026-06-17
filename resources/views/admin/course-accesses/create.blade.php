@extends('layouts.admin')

@section('title', 'Novo acesso a curso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Novo acesso a curso</h1>
        <a href="{{ route('admin.course-accesses.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.course-accesses.store') }}">
        @include('admin.course-accesses._form')
    </form>
</div>
@endsection
