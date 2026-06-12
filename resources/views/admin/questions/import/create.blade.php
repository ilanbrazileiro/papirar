@extends('layouts.admin')

@section('title', 'Importar questões')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Importar questões</h1>
            <p class="text-muted mb-0">Envie o CSV para pré-validação antes de gravar as questões no banco.</p>
        </div>
        <a href="{{ route('admin.question-import-batches.index') }}" class="btn btn-outline-secondary">Histórico de importações</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="{{ route('admin.questions.import.template') }}" class="btn btn-outline-primary">Baixar modelo oficial</a>
            <a href="{{ route('admin.questions.import.topics-csv') }}" class="btn btn-outline-secondary">Baixar disciplinas/tópicos</a>
            <a href="{{ route('admin.questions.import.source-materials-csv') }}" class="btn btn-outline-secondary">Baixar fontes/materiais</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Arquivo CSV</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.questions.import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="file" class="form-label">Selecione o arquivo CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info mb-3">
                    <strong>Novo fluxo:</strong> o arquivo será analisado primeiro. Na tela seguinte, você poderá revisar linhas válidas, erros e duplicidades antes de confirmar a importação. Todas as questões confirmadas entrarão como <strong>rascunho</strong>.
                </div>

                <button type="submit" class="btn btn-primary">Analisar CSV</button>
            </form>
        </div>
    </div>
</div>
@endsection
