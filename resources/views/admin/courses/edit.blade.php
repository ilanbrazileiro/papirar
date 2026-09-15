@extends('layouts.admin')

@section('title', 'Editar curso | Papirar')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h1 class="h3 mb-0">Editar curso</h1>
            <p class="text-muted mb-0">{{ $course->title }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if(
                $course->exists
                && $course->slug
                && $course->landing_enabled
                && $course->active
                && $course->is_public
            )
                <a
                    href="{{ route('site.course-landings.show', ['slug' => $course->slug]) }}"
                    class="btn btn-success"
                    target="_blank"
                    rel="noopener"
                >
                    Visualizar Landing Page
                </a>
            @elseif($course->exists && $course->slug)
                <span
                    class="btn btn-outline-secondary disabled"
                    title="A Landing Page precisa estar publicada, com curso ativo e público."
                >
                    Landing Page não publicada
                </span>
            @endif

            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-outline-secondary">Ver</a>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    @include('admin.courses._form')
</div>
@endsection
