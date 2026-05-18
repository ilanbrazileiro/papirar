@extends('layouts.admin')

@section('title', 'Editar questão')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1 class="m-0">Editar questão #{{ $question->id }}</h1>
                <p class="text-muted mb-0">Atualize o enunciado, imagens, alternativas e comentário.</p>
            </div>
            <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-outline-primary">
                    <i class="fas fa-eye"></i> Ver detalhes
                </a>
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
            $submitLabel = 'Salvar alterações';
            $formAction = route('admin.questions.update', $question);
            $formMethod = 'PUT';
        @endphp

        @include('admin.questions._form')
    </div>
</section>
@endsection
