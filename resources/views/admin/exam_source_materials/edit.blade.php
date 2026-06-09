@extends('layouts.admin')

@section('title', 'Fontes por concurso')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Fontes/bibliografias do concurso</h1>
            <div class="text-muted">
                {{ $exam->name ?? 'Concurso' }}
                @if($exam->corporation)
                    — {{ $exam->corporation->name }}
                @endif
            </div>
        </div>
        <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-secondary">Voltar ao concurso</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info">
        Vincule aqui quais fontes/bibliografias cada disciplina deste concurso pode usar. O filtro do aluno deve usar esses vínculos para evitar que ele estude questões de material errado.
    </div>

    <form method="POST" action="{{ route('admin.exams.source-materials.update', $exam) }}">
        @csrf
        @method('PUT')

        @forelse($examSubjects as $examSubject)
            @php
                $materials = $materialsBySubject->get($examSubject->subject_id, collect());
                $selected = $selectedByExamSubject[$examSubject->id] ?? [];
            @endphp

            <div class="card mb-3">
                <div class="card-header">
                    <strong>{{ $examSubject->subject_name }}</strong>
                </div>
                <div class="card-body">
                    @if($materials->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Nenhuma fonte/material ativo cadastrado para esta disciplina. Cadastre primeiro em <strong>Fontes e bibliografias</strong>.
                        </div>
                    @else
                        <div class="row">
                            @foreach($materials as $material)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check border rounded p-2 ps-4 h-100">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="material_{{ $examSubject->id }}_{{ $material->id }}"
                                            name="materials[{{ $examSubject->id }}][]"
                                            value="{{ $material->id }}"
                                            @checked(in_array((int) $material->id, $selected, true))
                                        >
                                        <label class="form-check-label" for="material_{{ $examSubject->id }}_{{ $material->id }}">
                                            <strong>{{ $material->title }}</strong><br>
                                            <small class="text-muted">
                                                {{ $material->corporation->name ?? 'Geral' }}
                                                @if($material->year) · {{ $material->year }} @endif
                                                @if($material->reference_code) · {{ $material->reference_code }} @endif
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-warning">
                Este concurso ainda não possui disciplinas vinculadas. Vincule as disciplinas antes de configurar as fontes.
            </div>
        @endforelse

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar fontes do concurso</button>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>
@endsection
