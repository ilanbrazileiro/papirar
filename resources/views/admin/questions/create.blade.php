@extends('layouts.admin')

@section('title', 'Nova questão')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1 class="m-0">Nova questão</h1>
                <p class="text-muted mb-0">Cadastre a questão com enunciado, imagem, alternativas e comentário.</p>
            </div>
            <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @php
            $submitLabel = 'Salvar questão';
            $formAction = route('admin.questions.store');
            $formMethod = 'POST';
        @endphp

        @include('admin.questions._form')
    </div>
</section>
@endsection
