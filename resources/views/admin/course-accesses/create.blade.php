@extends('admin.layouts.app')

@section('title', 'Novo acesso a curso')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h1 class="h3 mb-0">Novo acesso a curso</h1>
        <p class="text-muted mb-0">Libere manualmente um curso para um aluno.</p>
    </div>

    <form method="POST" action="{{ route('admin.course-accesses.store') }}">
        @include('admin.course-accesses._form')
    </form>
</div>
@endsection
