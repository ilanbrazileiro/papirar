@extends('layouts.admin')

@section('title', 'Nova questão')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cadastre uma questão completa com alternativas, vínculos e gabarito comentado.</h3>
        </div>
        <div class="card-body">
            @php($submitLabel = 'Salvar questão')
            @include('admin.questions._form')
        </div>
    </div>
@endsection
