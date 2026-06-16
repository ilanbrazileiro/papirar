@extends('layouts.admin')

@section('title', 'Curso | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">{{ $course->title }}</h1>
            <p class="text-muted mb-0">{{ $course->typeLabel() }} · a partir de R$ {{ number_format((float) $course->price, 2, ',', '.') }}/mês</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><strong>Dados do curso</strong></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Slug</dt>
                        <dd class="col-sm-8">{{ $course->slug }}</dd>

                        <dt class="col-sm-4">Corporação</dt>
                        <dd class="col-sm-8">{{ $course->corporation->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Concurso</dt>
                        <dd class="col-sm-8">{{ $course->exam->title ?? '-' }}</dd>

                        <dt class="col-sm-4">Preço mensal</dt>
                        <dd class="col-sm-8">R$ {{ number_format((float) $course->price, 2, ',', '.') }}</dd>

                        <dt class="col-sm-4">Preço trimestral</dt>
                        <dd class="col-sm-8">
                            @if($course->quarterly_price)
                                R$ {{ number_format((float) $course->quarterly_price, 2, ',', '.') }}
                            @else
                                <span class="text-muted">Não disponível</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Preço semestral</dt>
                        <dd class="col-sm-8">
                            @if($course->semiannual_price)
                                R$ {{ number_format((float) $course->semiannual_price, 2, ',', '.') }}
                            @else
                                <span class="text-muted">Não disponível</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($course->active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                            @if($course->is_public)
                                <span class="badge bg-info">Público</span>
                            @else
                                <span class="badge bg-light text-dark">Oculto</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Escopo do concurso</dt>
                        <dd class="col-sm-8">{{ $course->inherit_exam_scope ? 'Herdar quando houver concurso vinculado' : 'Usar escopo manual do curso' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><strong>Descrição</strong></div>
                <div class="card-body">
                    <p class="mb-2"><strong>Descrição curta:</strong> {{ $course->short_description ?: '-' }}</p>
                    <div>{!! nl2br(e($course->description ?: '-')) !!}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Disciplinas e tópicos vinculados diretamente ao curso</strong></div>
        <div class="card-body">
            @if($course->subjects->isEmpty())
                <p class="text-muted mb-0">Nenhuma disciplina vinculada diretamente. Se o curso herdar escopo de um concurso, as disciplinas/tópicos vêm do concurso vinculado.</p>
            @else
                @foreach($course->subjects as $subject)
                    <div class="mb-3">
                        <strong>{{ $subject->name }}</strong>
                        @php
                            $topics = $course->topics->where('subject_id', $subject->id);
                        @endphp
                        <div class="mt-2">
                            @forelse($topics as $topic)
                                <span class="badge bg-light text-dark border me-1 mb-1">{{ $topic->name }}</span>
                            @empty
                                <span class="text-muted small">Nenhum tópico específico selecionado.</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Fontes/Bibliografias</strong></div>
        <div class="card-body">
            @forelse($course->sourceMaterials as $material)
                <span class="badge bg-light text-dark border me-1 mb-1">{{ $material->title }}</span>
            @empty
                <p class="text-muted mb-0">Nenhuma fonte vinculada diretamente.</p>
            @endforelse
        </div>
    </div>

    @if($course->course_type === \App\Models\Course::TYPE_COMBO)
        <div class="card mb-3">
            <div class="card-header"><strong>Cursos incluídos no combo</strong></div>
            <div class="card-body">
                @forelse($course->includedCourses as $includedCourse)
                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $includedCourse->title }}</span>
                @empty
                    <p class="text-muted mb-0">Nenhum curso incluído no combo.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
