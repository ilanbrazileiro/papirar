@extends('layouts.admin')

@section('title', 'Visualizar concurso')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">{{ $exam->title }}</h1>
            <p class="text-muted mb-0">{{ $exam->corporation->name ?? 'Sem corporação' }} · {{ $exam->year }} · {{ $exam->exam_type }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><strong>Dados gerais</strong></div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-md-3">Status</dt>
                <dd class="col-md-9">{{ $exam->status === 'planned' ? 'Previsto' : 'Publicado' }}</dd>

                <dt class="col-md-3">Ativo</dt>
                <dd class="col-md-9">{{ $exam->active ? 'Sim' : 'Não' }}</dd>

                <dt class="col-md-3">Descrição</dt>
                <dd class="col-md-9">{!! nl2br(e($exam->description ?: 'Sem descrição.')) !!}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Disciplinas e tópicos cobrados</strong></div>
        <div class="card-body">
            @forelse($exam->examSubjects->sortBy('sort_order') as $examSubject)
                <div class="mb-4">
                    <h2 class="h5 mb-2">{{ $examSubject->subject->name ?? 'Disciplina removida' }}</h2>
                    @php
                        $topics = $examSubject->topicLinks->where('is_active', true)->sortBy('sort_order');
                    @endphp
                    @if($topics->isEmpty())
                        <p class="text-muted mb-0">Nenhum tópico vinculado.</p>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($topics as $topicLink)
                                <span class="badge bg-light text-dark border">{{ $topicLink->topic->name ?? 'Tópico removido' }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">Nenhuma disciplina vinculada a este concurso.</p>
            @endforelse
        </div>
    </div>
@endsection
