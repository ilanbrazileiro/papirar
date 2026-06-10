@extends('layouts.admin')

@section('title', 'Importar questões')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Importar questões</h1>
        <p class="text-muted mb-0">Importe questões em lote usando CSV separado por ponto e vírgula.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.questions.import.template') }}" class="btn btn-outline-primary">Baixar modelo oficial</a>
        <a href="{{ route('admin.questions.import.topics-csv') }}" class="btn btn-outline-secondary">Baixar disciplinas/tópicos</a>
        <a href="{{ route('admin.questions.import.source-materials-csv') }}" class="btn btn-outline-secondary">Baixar fontes/materiais</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Arquivo CSV</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.questions.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="file" class="form-label">Selecione o arquivo CSV *</label>
                <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="dry_run" id="dry_run" class="form-check-input" value="1" checked>
                <label for="dry_run" class="form-check-label">Simular importação antes de gravar no banco</label>
            </div>

            <button type="submit" class="btn btn-primary">Importar / Validar CSV</button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Instruções</h3>
    </div>
    <div class="card-body">
        <p>O modelo oficial agora aceita a coluna <code>source_material_id</code>, usada para indicar o manual, lei, norma ou edital que fundamenta a questão.</p>
        <p>Essa coluna pode ficar vazia enquanto a questão ainda não tiver uma fonte específica definida. O importador continua aceitando o cabeçalho antigo, sem <code>source_material_id</code>, para manter compatibilidade.</p>
    </div>
</div>

@if(session('import_report'))
    @php($report = session('import_report'))
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Relatório da importação</h3>
        </div>
        <div class="card-body">
            <p><strong>Linhas validadas:</strong> {{ $report['validated_rows'] ?? 0 }}</p>
            <p><strong>Questões inseridas:</strong> {{ $report['inserted'] ?? 0 }}</p>

            @if(!empty($report['errors']))
                <div class="alert alert-danger">
                    <strong>Erros encontrados:</strong>
                    <ul class="mb-0">
                        @foreach($report['errors'] as $error)
                            <li>Linha {{ $error['line'] ?? '-' }}: {{ $error['message'] ?? 'Erro não informado.' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($report['expected_header']))
                <p class="mb-1"><strong>Cabeçalho esperado:</strong></p>
                <pre class="bg-light p-2 border rounded">{{ implode(';', $report['expected_header']) }}</pre>
            @endif

            @if(!empty($report['received_header']))
                <p class="mb-1"><strong>Cabeçalho recebido:</strong></p>
                <pre class="bg-light p-2 border rounded">{{ implode(';', $report['received_header']) }}</pre>
            @endif
        </div>
    </div>
@endif
@endsection
