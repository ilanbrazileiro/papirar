@extends('layouts.student')

@section('title', $course->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div><h1 class="page-title">{{ $course->title }}</h1><p class="page-subtitle">{{ $course->short_description ?: 'Curso liberado para estudo por questões.' }}</p></div>
        <div class="d-flex flex-wrap gap-2"><a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary">Meus cursos</a><a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar</a><a href="{{ route('student.course.simulated.index', $course) }}" class="btn btn-outline-primary">Simulados</a></div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Questões disponíveis</div><div class="value">{{ $totalQuestions }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Disciplinas</div><div class="value">{{ $subjects->count() }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Tópicos</div><div class="value">{{ $topics->count() }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Fontes</div><div class="value">{{ $sourceMaterials->count() }}</div></div></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 mb-4"><div class="section-title">Disciplinas do curso</div>@if($subjects->isEmpty())<div class="small-muted">Nenhuma disciplina vinculada ao curso.</div>@else<div class="row g-2">@foreach($subjects as $subject)<div class="col-md-6"><div class="border rounded-4 p-3 bg-white">{{ $subject->name }}</div></div>@endforeach</div>@endif</div>
            <div class="card-soft p-4"><div class="section-title">Tópicos do curso</div>@if($topics->isEmpty())<div class="small-muted">Nenhum tópico vinculado ao curso.</div>@else<div class="row g-2">@foreach($topics as $topic)<div class="col-md-6"><div class="border rounded-4 p-3 bg-white">{{ $topic->name }}</div></div>@endforeach</div>@endif</div>
        </div>
        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4"><div class="section-title">Ações</div><div class="d-grid gap-2"><a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Iniciar estudo</a><a href="{{ route('student.course-simulated.index', $course) }}" class="btn btn-outline-primary">Criar simulado</a><button class="btn btn-outline-secondary" type="button" disabled>Desempenho em breve</button></div></div>
            <div class="card-soft p-4"><div class="section-title">Fontes/Bibliografias</div>@if($sourceMaterials->isEmpty())<div class="small-muted">Este curso ainda não possui filtro por fonte.</div>@else<ul class="list-clean">@foreach($sourceMaterials as $sourceMaterial)<li class="py-2">{{ $sourceMaterial->title }}</li>@endforeach</ul>@endif</div>
        </div>
    </div>
@endsection
