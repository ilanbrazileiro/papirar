@extends('layouts.student')

@section('title', 'Meus cursos')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Meus cursos</h1>
            <p class="page-subtitle">Acesse os cursos que você assinou e estude somente dentro do escopo liberado.</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">Voltar ao dashboard</a>
    </div>

    @if($courseAccesses->isEmpty())
        <div class="card-soft p-4">
            <div class="section-title mb-2">Nenhum curso ativo encontrado</div>
            <p class="small-muted mb-0">
                Você ainda não possui acesso ativo a cursos. Assim que uma assinatura for liberada, seus cursos aparecerão aqui.
            </p>
        </div>
    @else
        <div class="row g-4">
            @foreach($courseAccesses as $access)
                @php
                    $course = $access->course;
                    $questionCount = $course ? ($courseQuestionCounts[$course->id] ?? 0) : 0;
                @endphp

                @if($course)
                    <div class="col-md-6 col-xl-4">
                        <div class="card-soft p-4 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="section-title mb-1">{{ $course->title }}</div>
                                    <div class="small-muted">{{ $course->typeLabel() }}</div>
                                </div>
                                <span class="badge text-bg-success align-self-start">Ativo</span>
                            </div>

                            @if($course->short_description)
                                <p class="small-muted">{{ $course->short_description }}</p>
                            @endif

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="stats-card p-3">
                                        <div class="label">Questões</div>
                                        <div class="value fs-4">{{ $questionCount }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-card p-3">
                                        <div class="label">Acesso até</div>
                                        <div class="fw-bold mt-2">
                                            {{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-grid gap-2">
                                <a href="{{ route('student.courses.show', $course) }}" class="btn btn-primary">Entrar no curso</a>
                                <a href="{{ route('student.courses.study', $course) }}" class="btn btn-outline-primary">Estudar agora</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
@endsection
