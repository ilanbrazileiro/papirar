@extends('layouts.admin')

@section('title', 'Editar questão #' . $question->id)

@section('content')

<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Editar Questão # {{ $question->id }}</h1>
    </div>
    <div class="col-sm-6">
        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Deseja excluir esta questão?');">
                @csrf
        <div class="btn-group float-sm-right">
            @include('admin.questions.partials.preview_button')
            <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> Ver detalhes
            </a>
                        
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="far fa-trash-alt"></i> Excluir
                </button>
            </form>
        </div>        
    </div>
</div>


    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Atualize os dados mantendo os vínculos e as alternativas consistentes.</h3>
        </div>
        <div class="card-body">
            @php($submitLabel = 'Salvar alterações')
            @include('admin.questions._form')
        </div>
    </div>
@endsection
