@extends('layouts.admin')

@section('title', 'Dashboard de Conteúdo')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard de Conteúdo</h1>
            <p class="text-muted mb-0">Acompanhe os produtos do Papirar e a cobertura do banco de questões.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nova questão</a>
            <a href="{{ route('admin.questions.import.create') }}" class="btn btn-outline-primary"><i class="fas fa-file-import mr-1"></i> Importar questões</a>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-secondary"><i class="fas fa-box-open mr-1"></i> Novo produto</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php
            $summaryCards = [
                ['label' => 'Produtos', 'value' => $stats['courses_total'] ?? 0, 'hint' => ($stats['courses_active'] ?? 0).' ativos · '.($stats['courses_public'] ?? 0).' públicos', 'url' => route('admin.courses.index')],
                ['label' => 'Total de questões', 'value' => $stats['questions_total'] ?? 0, 'hint' => (($stats['questions_published'] ?? 0) + ($stats['questions_reviewed'] ?? 0)).' visíveis', 'url' => route('admin.questions.index')],
                ['label' => 'Revisadas', 'value' => $stats['questions_reviewed'] ?? 0, 'hint' => 'Validadas editorialmente', 'url' => route('admin.questions.index', ['status' => 'reviewed'])],
                ['label' => 'Rascunhos', 'value' => $stats['questions_draft'] ?? 0, 'hint' => 'Pendentes de publicação', 'url' => route('admin.questions.drafts')],
            ];
        @endphp

        @foreach($summaryCards as $card)
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-semibold">{{ $card['label'] }}</div>
                        <div class="display-5 fw-bold mb-1">{{ number_format($card['value'], 0, ',', '.') }}</div>
                        <div class="small text-muted">{{ $card['hint'] }}</div>
                        <div class="mt-3"><a href="{{ $card['url'] }}" class="btn btn-sm btn-outline-secondary">Ver detalhes</a></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h5 mb-1">Produtos</h2>
            <p class="text-muted mb-0">Cobertura de conteúdo e acessos ativos de cada produto comercial.</p>
        </div>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-primary">Todos os produtos</a>
    </div>

    @if($products->count())
        <div class="row g-3">
            @foreach($products as $product)
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        @if($product->coverImageUrl())
                            <img src="{{ $product->coverImageUrl() }}" alt="{{ $product->title }}" class="card-img-top" style="height:155px;object-fit:cover;">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <div class="text-muted small">{{ $product->corporation->name ?? 'Produto geral' }}</div>
                                    <h3 class="h5 mb-1">{{ $product->title }}</h3>
                                    <div class="small text-muted">{{ $product->typeLabel() }} @if($product->exam) · {{ $product->exam->title }} @endif</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $product->active ? 'success' : 'secondary' }}">{{ $product->active ? 'Ativo' : 'Inativo' }}</span>
                                    <div class="mt-1"><span class="badge bg-{{ $product->is_public ? 'primary' : 'light text-dark' }}">{{ $product->is_public ? 'Público' : 'Oculto' }}</span></div>
                                </div>
                            </div>

                            <div class="row g-2 my-3">
                                <div class="col-4"><div class="border rounded p-2 text-center h-100"><div class="fw-semibold">{{ number_format($product->questions_visible ?? 0, 0, ',', '.') }}</div><div class="small text-muted">visíveis</div></div></div>
                                <div class="col-4"><div class="border rounded p-2 text-center h-100"><div class="fw-semibold">{{ number_format($product->subjects_count ?? 0, 0, ',', '.') }}</div><div class="small text-muted">disciplinas</div></div></div>
                                <div class="col-4"><div class="border rounded p-2 text-center h-100"><div class="fw-semibold">{{ number_format($product->topics_count ?? 0, 0, ',', '.') }}</div><div class="small text-muted">tópicos</div></div></div>
                            </div>

                            <div class="small text-muted mb-3">
                                {{ number_format($product->questions_total ?? 0, 0, ',', '.') }} questões compatíveis ·
                                {{ number_format($product->questions_draft ?? 0, 0, ',', '.') }} rascunhos ·
                                {{ number_format($product->active_accesses_count ?? 0, 0, ',', '.') }} acessos ativos
                            </div>

                            @if(($product->questions_visible ?? 0) === 0)
                                <div class="alert alert-warning small py-2">Este produto ainda não possui questões publicadas ou revisadas compatíveis.</div>
                            @endif

                            <div class="mt-auto d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.courses.edit', $product) }}" class="btn btn-sm btn-outline-primary">Editar produto</a>
                                <a href="{{ route('admin.reports.courses.show', $product) }}" class="btn btn-sm btn-outline-secondary">Ver relatório</a>
                                <a href="{{ route('admin.questions.index', array_filter(['corporation_id' => $product->corporation_id])) }}" class="btn btn-sm btn-outline-secondary">Ver questões</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <h3 class="h5">Nenhum produto cadastrado</h3>
                <p class="text-muted mb-3">Cadastre o primeiro produto para organizar o conteúdo comercial do Papirar.</p>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">Cadastrar produto</a>
            </div>
        </div>
    @endif
</div>
@endsection
