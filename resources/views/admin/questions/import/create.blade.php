@extends('layouts.admin')

@section('title', 'Importar questões')

@section('content')
@php
    $activeTab = request('tab', old('csv_content') ? 'paste' : 'file');
    if (! in_array($activeTab, ['file', 'paste'], true)) {
        $activeTab = 'file';
    }
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Importar questões</h1>
            <p class="text-muted mb-0">Analise o CSV antes de gravar. Todas as questões confirmadas entram como rascunho.</p>
        </div>
        <a href="{{ route('admin.question-import-batches.index') }}" class="btn btn-outline-secondary">Histórico de importações</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Verifique os campos:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="{{ route('admin.questions.import.template') }}" class="btn btn-outline-primary">Baixar modelo oficial</a>
            <a href="{{ route('admin.questions.import.topics-csv') }}" class="btn btn-outline-secondary">Baixar disciplinas/tópicos</a>
            <a href="{{ route('admin.questions.import.source-materials-csv') }}" class="btn btn-outline-secondary">Baixar fontes/materiais</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a href="{{ route('admin.questions.import.create', ['tab' => 'file']) }}"
                       class="nav-link {{ $activeTab === 'file' ? 'active' : '' }}">
                        Enviar arquivo CSV
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.questions.import.create', ['tab' => 'paste']) }}"
                       class="nav-link {{ $activeTab === 'paste' ? 'active' : '' }}">
                        Colar linhas CSV
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            @if($activeTab === 'file')
                <form method="POST" action="{{ route('admin.questions.import.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="file" class="form-label">Selecione o arquivo CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt">
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info mb-3">
                        <strong>Fluxo seguro:</strong> o arquivo será analisado primeiro. Na tela seguinte, você verá linhas válidas, erros e duplicidades antes de confirmar a importação. Tudo que for confirmado entrará como <strong>rascunho</strong>.
                    </div>

                    <button type="submit" class="btn btn-primary">Analisar arquivo CSV</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.questions.import.direct') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="csv_content" class="form-label">Cole o conteúdo do CSV <span class="text-danger">*</span></label>
                        <textarea name="csv_content" id="csv_content" rows="18" class="form-control font-monospace @error('csv_content') is-invalid @enderror" placeholder="Cole aqui o cabeçalho e as linhas do CSV separadas por ponto e vírgula">{{ old('csv_content') }}</textarea>
                        @error('csv_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Use o mesmo cabeçalho do modelo oficial. Não é necessário criar arquivo para importar.</small>
                    </div>

                    <div class="alert alert-warning mb-3">
                        <strong>Importante:</strong> mesmo que a coluna <code>status</code> venha como <code>published</code> ou <code>reviewed</code>, o Papirar importará as questões como <strong>rascunho</strong> para revisão editorial.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cabeçalho esperado no modelo novo</label>
                        <pre class="bg-light border rounded p-3 small mb-0">corporation_id;exam_id;subject_id;topic_id;statement;question_type;difficulty;source_type;source_reference;source_material_id;commented_answer;status;alternative_a;alternative_b;alternative_c;alternative_d;alternative_e;correct_letter</pre>
                    </div>

                    <button type="submit" class="btn btn-primary">Analisar CSV colado</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
