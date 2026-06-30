@extends('layouts.admin')

@section('title', 'Detalhes da questão')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Questão #{{ $question->id }}</h1>
        <p class="text-muted mb-0">Visualização completa da questão cadastrada.</p>
    </div>
    <div>
        <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Dados da questão</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-2"><strong>Corporação:</strong> {{ $question->corporation->name ?? '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Disciplina:</strong> {{ $question->subject->name ?? '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Assunto:</strong> {{ $question->topic->name ?? '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Concurso:</strong> {{ $question->exam->title ?? '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Banca:</strong> {{ $question->examBoard->name ?? '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Dificuldade:</strong> {{ ucfirst($question->difficulty) }}</div>
            <div class="col-md-4 mb-2"><strong>Origem:</strong> {{ $question->source_type }}</div>
            <div class="col-md-4 mb-2"><strong>Status:</strong> {{ $question->status }}</div>
            <div class="col-md-8 mb-2"><strong>Referência:</strong> {{ $question->source_reference ?: '-' }}</div>
        </div>

        <div class="mt-2">
            <strong>Fonte / Bibliografia:</strong>
            @if($question->sourceMaterial)
                {{ $question->sourceMaterial->title }}
                @if($question->sourceMaterial->year)
                    - {{ $question->sourceMaterial->year }}
                @endif
                @if($question->sourceMaterial->reference_code)
                    ({{ $question->sourceMaterial->reference_code }})
                @endif
            @else
                <span class="text-muted">Não informada</span>
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Enunciado</h3>
    </div>
    <div class="card-body">
        {!! $question->statement !!}
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title mb-0">Alternativas</h3>
    </div>
    <div class="card-body">
        @foreach($question->alternatives->sortBy('letter') as $alternative)
            <div class="border rounded p-3 mb-2 {{ $alternative->is_correct ? 'border-success' : '' }}">
                <strong>{{ $alternative->letter }})</strong>
                {!! $alternative->text !!}
                @if($alternative->is_correct)
                    <span class="badge bg-success ms-2">Correta</span>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Comentário / gabarito comentado</h3>
    </div>
    <div class="card-body">
        {!! $question->commented_answer ?: 'Sem comentário cadastrado.' !!}
    </div>
</div>
@endsection
