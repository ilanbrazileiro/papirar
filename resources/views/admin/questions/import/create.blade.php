@extends('layouts.admin')

@section('title', 'Importar questões')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Importar questões</h1>
            <p class="text-muted mb-0">Importe questões em lote usando CSV separado por ponto e vírgula.</p>
        </div>
        @if(Route::has('admin.question-import-batches.index'))
            <a href="{{ route('admin.question-import-batches.index') }}" class="btn btn-outline-secondary">Histórico de importações</a>
        @endif
    </div>

    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.questions.import.template') }}" class="btn btn-sm btn-outline-primary">Baixar modelo oficial</a>
        <a href="{{ route('admin.questions.import.topics-csv') }}" class="btn btn-sm btn-outline-primary">Baixar disciplinas/tópicos</a>
        @if(Route::has('admin.questions.import.source-materials-csv'))
            <a href="{{ route('admin.questions.import.source-materials-csv') }}" class="btn btn-sm btn-outline-primary">Baixar fontes/materiais</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Arquivo CSV</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.questions.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Selecione o arquivo CSV *</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="dry_run" value="1" class="form-check-input" id="dry_run">
                    <label for="dry_run" class="form-check-label">Simular importação antes de gravar no banco</label>
                </div>

                <button type="submit" class="btn btn-primary">Importar / Validar CSV</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Regras desta etapa</strong></div>
        <div class="card-body">
            <ul class="mb-0">
                <li>As questões importadas entram sempre como <strong>rascunho</strong>, mesmo que o CSV informe outro status.</li>
                <li>O sistema registra o lote de importação para histórico.</li>
                <li>Linhas importadas com sucesso não mantêm o conteúdo bruto duplicado na tabela de linhas.</li>
                <li>Linhas com erro ou duplicidade ficam registradas para conferência.</li>
                <li>A verificação de duplicidade deste lote é apenas por enunciado normalizado idêntico.</li>
            </ul>
        </div>
    </div>

    @if(session('import_report'))
        @php($report = session('import_report'))
        <div class="card">
            <div class="card-header"><strong>Relatório da importação</strong></div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><strong>Linhas validadas:</strong> {{ $report['validated_rows'] ?? 0 }}</div>
                    <div class="col-md-3"><strong>Questões inseridas:</strong> {{ $report['inserted'] ?? 0 }}</div>
                    <div class="col-md-3"><strong>Lote:</strong>
                        @if(!empty($report['batch_id']) && Route::has('admin.question-import-batches.show'))
                            <a href="{{ route('admin.question-import-batches.show', $report['batch_id']) }}">#{{ $report['batch_id'] }}</a>
                        @else
                            -
                        @endif
                    </div>
                </div>

                @if(!empty($report['errors']))
                    <h5>Erros encontrados</h5>
                    <ul>
                        @foreach($report['errors'] as $error)
                            <li>Linha {{ $error['line'] ?? '-' }}: {{ $error['message'] ?? 'Erro não informado.' }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($report['duplicates']))
                    <h5>Possíveis duplicidades</h5>
                    <ul>
                        @foreach($report['duplicates'] as $duplicate)
                            <li>
                                Linha {{ $duplicate['line'] ?? '-' }}: {{ $duplicate['message'] ?? 'Duplicidade encontrada.' }}
                                @if(!empty($duplicate['question_id']))
                                    <a href="{{ route('admin.questions.edit', $duplicate['question_id']) }}">Ver questão #{{ $duplicate['question_id'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($report['expected_header']))
                    <h5>Cabeçalho esperado</h5>
                    <pre>{{ implode(';', $report['expected_header']) }}</pre>
                @endif

                @if(!empty($report['received_header']))
                    <h5>Cabeçalho recebido</h5>
                    <pre>{{ implode(';', $report['received_header']) }}</pre>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
