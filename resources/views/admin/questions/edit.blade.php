@extends('layouts.admin')

@section('title', 'Editar questão #' . $question->id)

@section('page_actions')
    <a href="{{ route('admin.questions.show', $question) }}" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-eye"></i> Ver detalhes
    </a>
@endsection

@section('content')
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
