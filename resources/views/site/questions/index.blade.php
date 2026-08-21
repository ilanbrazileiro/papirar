@extends('site.site_layout')

@section('title', 'Questões de Concursos por Disciplina e Tópico | Papirar')
@section('meta_description', 'Resolva questões de concursos organizadas por disciplina e tópico no Papirar. Encontre questões de Direito, Português, Matemática e outras áreas.')
@section('canonical', $canonicalUrl)
@section('og_title', 'Questões de Concursos | Papirar')
@section('og_description', 'Encontre questões organizadas por disciplina e tópico e estude por questões no Papirar.')

@push('head')
<link rel="stylesheet" href="{{ asset('css/questions-catalog.css') }}">
@endpush

@section('content')
<section class="catalog-hero">
    <div class="site-container">
        <span class="catalog-eyebrow">Banco de questões</span>
        <h1>Questões de concursos por disciplina e tópico</h1>
        <p>
            Explore {{ number_format($totalQuestions, 0, ',', '.') }} questões públicas
            organizadas para facilitar sua preparação.
        </p>
    </div>
</section>

<section class="catalog-section">
    <div class="site-container">
        <div class="catalog-heading">
            <h2>Escolha uma disciplina</h2>
            <p>Acesse as questões e depois refine pelos tópicos disponíveis.</p>
        </div>

        <div class="subject-grid">
            @foreach($subjects as $subject)
                <a href="{{ route('site.questions.subject', ['subjectSlug' => $subject->slug]) }}" class="subject-card">
                    <span>{{ $subject->name }}</span>
                    <strong>{{ number_format($subject->public_questions_count, 0, ',', '.') }} questões</strong>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="catalog-section catalog-light">
    <div class="site-container">
        <div class="catalog-heading">
            <h2>Questões adicionadas recentemente</h2>
        </div>

        <div class="question-list-public">
            @foreach($latestQuestions as $item)
                <article class="question-preview-card">
                    <div class="question-preview-meta">
                        @if($item['subject_name'])<span>{{ $item['subject_name'] }}</span>@endif
                        @if($item['topic_name'])<span>{{ $item['topic_name'] }}</span>@endif
                    </div>
                    <p>{{ $item['statement_excerpt'] }}</p>
                    <a href="{{ $item['url'] }}">Ver questão e alternativas →</a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
