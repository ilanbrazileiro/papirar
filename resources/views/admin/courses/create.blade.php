@extends('layouts.admin')

@section('title', 'Novo curso | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Novo curso</h1>
            <p class="text-muted mb-0">Cadastre um produto de estudo para venda por assinatura mensal.</p>
        </div>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    @include('admin.courses._form')
</div>
@endsection
