@extends('admin.layout')

@section('title', 'Detalhes da questão')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Questão #{{ $question->id }}</h1>
            <p class="page-subtitle">Visualização completa da questão cadastrada.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.questions.edit', $question) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="panel p-4 mb-4">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-muted mb-1">Corporação</div>
                <div class="fw-semibold">{{ $question->corporation->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted mb-1">Disciplina</div>
                <div class="fw-semibold">{{ $question->subject->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted mb-1">Assunto</div>
                <div>{{ $question->topic->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted mb-1">Concurso</div>
                <div>{{ $question->exam->title ?? '-' }}</div>
            </div>
            <div class="col-md-2">
                <div class="text-muted mb-1">Dificuldade</div>
                <div>{{ ucfirst($question->difficulty) }}</div>
            </div>
            <div class="col-md-2">
                <div class="text-muted mb-1">Origem</div>
                <div>{{ $question->source_type }}</div>
            </div>
            <div class="col-md-2">
                <div class="text-muted mb-1">Status</div>
                <div>{{ $question->status }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted mb-1">Referência</div>
                <div>{{ $question->source_reference ?: '-' }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted mb-1">Enunciado</div>
                <div class="border rounded-4 p-3">{!! $question->statement !!}</div>
            </div>
        </div>
    </div>

    <div class="panel p-4 mb-4">
        <div class="fw-bold mb-3">Alternativas</div>
        <div class="row g-3">
            @foreach($question->alternatives->sortBy('letter') as $alternative)
                <div class="col-12">
                    <div class="border rounded-4 p-3 {{ $alternative->is_correct ? 'border-success' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-bold mb-2">{{ $alternative->letter }})</div>
                                <div>{!! $alternative->text !!}</div>
                            </div>
                            @if($alternative->is_correct)
                                <span class="badge text-bg-success">Correta</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="panel p-4">
        <div class="fw-bold mb-3">Comentário / gabarito comentado</div>
        <div class="border rounded-4 p-3">
            {!! $question->commented_answer ?: 'Sem comentário cadastrado.' !!}
        </div>
    </div>
@endsection
