@extends('admin.layout')

@section('title', 'Importar questões em lote')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Importar questões em lote</h1>
            <p class="page-subtitle">Importe entre 50 e 100 questões por vez com validação, modo simulação e relatório de erros.</p>
        </div>
        <a href="{{ route('admin.questions.import.template') }}" class="btn btn-outline-primary">Baixar modelo CSV</a>
        <a href="{{ route('admin.questions.import.topics-csv') }}" class="btn btn-outline-primary"> <i class="fas fa-download"></i>
        Baixar lista de subjects e topics
        </a>
    </div>

    <div class="panel p-4 mb-4">
        <div class="fw-bold mb-2">Melhor formato para o seu caso</div>
        <p class="mb-2">Para o Papirar, o melhor caminho é <strong>CSV padronizado</strong>. Para 50 a 100 questões por vez, isso é simples, rápido e controlável.</p>
        <p class="mb-0 text-muted">Evite PDF, Word ou planilha sem padrão. Isso aumenta erro e retrabalho.</p>
    </div>

    <div class="panel p-4 p-md-5">
        <form method="POST" action="{{ route('admin.questions.import.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Arquivo CSV</label>
                <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                <div class="form-text">Use o modelo oficial e mantenha o separador como ponto e vírgula (;).</div>
            </div>

            <div class="form-check form-switch mb-4">
                <input type="hidden" name="dry_run" value="0">
                <input class="form-check-input" type="checkbox" id="dry_run" name="dry_run" value="1" checked>
                <label class="form-check-label" for="dry_run">Executar primeiro em modo simulação, sem salvar no banco</label>
            </div>

            <button class="btn btn-primary">Processar importação</button>
        </form>
    </div>

    @if(session('import_report'))
        @php($report = session('import_report'))
        <div class="panel p-4 mt-4">
            <div class="fw-bold mb-3">Resultado da importação</div>

            <div class="mb-2"><strong>Mensagem:</strong> {{ $report['message'] ?? '-' }}</div>
            <div class="mb-2"><strong>Linhas validadas:</strong> {{ $report['validated_rows'] ?? 0 }}</div>
            <div class="mb-2"><strong>Inseridas:</strong> {{ $report['inserted'] ?? 0 }}</div>

            @if(!empty($report['errors']))
                <div class="alert alert-danger mt-3 mb-0">
                    <div class="fw-semibold mb-2">Erros encontrados</div>
                    <ul class="mb-0 ps-3">
                        @foreach($report['errors'] as $error)
                            <li>{{ $error['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="panel p-4 mt-4">
        <div class="fw-bold mb-3">Cabeçalho esperado</div>
        <pre class="mb-0 small">corporation_id;exam_id;subject_id;topic_id;statement;question_type;difficulty;source_type;source_reference;commented_answer;status;alternative_a;alternative_b;alternative_c;alternative_d;alternative_e;correct_letter</pre>
    </div>
@endsection
