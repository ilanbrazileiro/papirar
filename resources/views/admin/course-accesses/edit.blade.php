@extends('layouts.admin')

@section('title', 'Editar acesso a curso')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h1 class="h3 mb-0">Editar acesso a curso</h1>
        <p class="text-muted mb-0">Ajuste período, status ou bônus de dias.</p>
    </div>

    <form method="POST" action="{{ route('admin.course-accesses.update', $access) }}">
        @method('PUT')
        @include('admin.course-accesses._form')
    </form>
</div>
@endsection
