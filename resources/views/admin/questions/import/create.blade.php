@extends('layouts.admin')

@section('title', 'Importar questões')

@section('content')
@php
    $activeTab = request('tab', old('csv_content') ? 'paste' : 'file');
    if (! in_array($activeTab, ['ai', 'file', 'paste'], true)) {
        $activeTab = 'file';
    }

    $officialHeader = 'corporation_id;exam_id;subject_id;topic_id;exam_board_id;exam_board;statement;question_type;difficulty;source_type;source_reference;source_material_id;commented_answer;status;alternative_a;alternative_b;alternative_c;alternative_d;alternative_e;correct_letter';
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Importar questões</h1>
            <p class="text-muted mb-0">Extraia de documentos ou analise um CSV. Todas as questões confirmadas entram como rascunho.</p>
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

    <div class="alert alert-info">
        <strong>Banca organizadora:</strong> agora o CSV aceita <code>exam_board_id</code> ou <code>exam_board</code>.
        Para facilitar o uso com GPT, você pode deixar <code>exam_board_id</code> vazio e preencher <code>exam_board</code> com o nome cadastrado, por exemplo <code>FGV</code>.
    </div>

    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a href="{{ route('admin.questions.import.create', ['tab' => 'ai']) }}"
                       class="nav-link {{ $activeTab === 'ai' ? 'active' : '' }}">
                        Extrair com IA
                    </a>
                </li>
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
            @if($activeTab === 'ai')
                <form method="POST" action="{{ route('admin.questions.import.ai') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="alert alert-info">
                        <strong>Arquivos:</strong> envie obrigatoriamente o documento com as questões. Se o gabarito vier em outro arquivo, envie-o no campo separado. Se ele já estiver no mesmo documento, deixe o segundo campo vazio.
                        O extrator associa o gabarito, sugere disciplina/tópico usando apenas a taxonomia existente e gera a referência automaticamente. Nenhuma questão é publicada sem conferência.
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="source_type" class="form-label">Origem das questões <span class="text-danger">*</span></label>
                            <select name="source_type" id="source_type" class="form-control @error('source_type') is-invalid @enderror" required>
                                <option value="exam" @selected(old('source_type', 'exam') === 'exam')>Prova oficial</option>
                                <option value="authored" @selected(old('source_type') === 'authored')>Questões autorais</option>
                                <option value="adapted" @selected(old('source_type') === 'adapted')>Questões adaptadas</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="corporation_id" class="form-label">Corporação</label>
                            <select name="corporation_id" id="corporation_id" class="form-control @error('corporation_id') is-invalid @enderror">
                                <option value="">Não informar</option>
                                @foreach($corporations as $corporation)
                                    <option value="{{ $corporation->id }}" @selected((string) old('corporation_id') === (string) $corporation->id)>{{ $corporation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="exam_id" class="form-label">Vincular a prova cadastrada</label>
                            <select name="exam_id" id="exam_id" class="form-control @error('exam_id') is-invalid @enderror">
                                <option value="">Não vincular</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" data-corporation="{{ $exam->corporation_id }}" @selected((string) old('exam_id') === (string) $exam->id)>
                                        {{ $exam->title }}{{ $exam->year ? ' — '.$exam->year : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Opcional. Use quando essa prova já existir no Papirar.</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="exam_board_id" class="form-label">Banca</label>
                            <select name="exam_board_id" id="exam_board_id" class="form-control @error('exam_board_id') is-invalid @enderror">
                                <option value="">Não informar</option>
                                @foreach($examBoards as $board)
                                    <option value="{{ $board->id }}" @selected((string) old('exam_board_id') === (string) $board->id)>{{ $board->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="exam_year" class="form-label">Ano da prova <span class="text-danger exam-required-marker">*</span></label>
                            <input type="number" name="exam_year" id="exam_year" min="1900" max="{{ now()->year + 1 }}" value="{{ old('exam_year', now()->year) }}" class="form-control @error('exam_year') is-invalid @enderror">
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="exam_reference" class="form-label">Cargo/concurso <span class="text-danger exam-required-marker">*</span></label>
                            <input type="text" name="exam_reference" id="exam_reference" maxlength="180" value="{{ old('exam_reference') }}" class="form-control @error('exam_reference') is-invalid @enderror" placeholder="Ex.: Soldado PMERJ, CFO CBMERJ, Analista Judiciário">
                            <small class="form-text text-muted">Obrigatório para prova oficial. A referência será BANCA (quando houver) - ANO - CARGO/CONCURSO.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="source_file" class="form-label">Arquivo das questões/prova <span class="text-danger">*</span></label>
                            <input type="file" name="source_file" id="source_file" class="form-control @error('source_file') is-invalid @enderror" accept=".pdf,.docx,.jpg,.jpeg,.png" required>
                            <small class="form-text text-muted">PDF, DOCX, JPG ou PNG. Até 15 MB.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="answer_file" class="form-label">Arquivo separado do gabarito</label>
                            <input type="file" name="answer_file" id="answer_file" class="form-control @error('answer_file') is-invalid @enderror" accept=".pdf,.docx,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">Deixe vazio somente quando o gabarito estiver dentro do arquivo da prova. Até 8 MB.</small>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        DOCX é lido como texto. Se houver imagens, fórmulas ou diagramação indispensável, envie a versão em PDF.
                    </div>

                    <button type="submit" class="btn btn-primary">Enviar para extração</button>
                </form>
            @elseif($activeTab === 'file')
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

                    <div class="mb-3">
                        <label class="form-label">Cabeçalho atual recomendado</label>
                        <pre class="bg-light border rounded p-3 small mb-0">{{ $officialHeader }}</pre>
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
                        <label class="form-label">Cabeçalho atual recomendado</label>
                        <pre class="bg-light border rounded p-3 small mb-0">{{ $officialHeader }}</pre>
                    </div>

                    <button type="submit" class="btn btn-primary">Analisar CSV colado</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sourceType = document.getElementById('source_type');
    const year = document.getElementById('exam_year');
    const reference = document.getElementById('exam_reference');
    const exam = document.getElementById('exam_id');
    const board = document.getElementById('exam_board_id');
    const markers = document.querySelectorAll('.exam-required-marker');

    function updateExamRequirements() {
        const isExam = sourceType && sourceType.value === 'exam';
        const isAuthored = sourceType && sourceType.value === 'authored';
        year.required = isExam;
        reference.required = isExam;
        [exam, board, year, reference].forEach(field => {
            field.disabled = isAuthored;
        });
        markers.forEach(marker => marker.classList.toggle('d-none', !isExam));
    }

    sourceType.addEventListener('change', updateExamRequirements);
    updateExamRequirements();
});
</script>
@endpush
