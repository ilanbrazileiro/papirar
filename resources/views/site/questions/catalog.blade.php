@extends('site.site_layout')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)

@push('head')
<link rel="stylesheet" href="{{ asset('css/questions-catalog.css') }}">
@php
    $breadcrumb = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Papirar',
            'item' => route('site.home'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Questões',
            'item' => route('site.questions.index'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $subject->name,
            'item' => route('site.questions.subject', ['subjectSlug' => $subject->slug]),
        ],
    ];

    if ($topic) {
        $breadcrumb[] = [
            '@type' => 'ListItem',
            'position' => 4,
            'name' => $topic->name,
            'item' => $canonicalUrl,
        ];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $breadcrumb,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="catalog-hero catalog-hero-small">
    <div class="site-container">
        <nav class="catalog-breadcrumb" aria-label="Navegação estrutural">
            <a href="{{ route('site.home') }}">Início</a>
            <span>›</span>
            <a href="{{ route('site.questions.index') }}">Questões</a>
            @if($topic)
                <span>›</span>
                <a href="{{ route('site.questions.subject', ['subjectSlug' => $subject->slug]) }}">{{ $subject->name }}</a>
            @endif
        </nav>
        <h1>{{ $heading }}</h1>
        <p>{{ $intro }}</p>
    </div>
</section>

<section class="catalog-section">
    <div class="site-container catalog-layout">
        <main>
            @if(!$topic && $topics->isNotEmpty())
                <div class="topic-cloud">
                    @foreach($topics as $item)
                        <a href="{{ route('site.questions.topic', [
                            'subjectSlug' => $subject->slug,
                            'topicSlug' => $item->slug,
                        ]) }}">
                            {{ $item->name }}
                            <span>{{ $item->public_questions_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="question-list-public">
                @forelse($questions as $item)
                    <article class="question-preview-card">
                        <div class="question-preview-meta">
                            @if($item['topic_name'])<span>{{ $item['topic_name'] }}</span>@endif
                            @if($item['exam_board_name'])<span>{{ $item['exam_board_name'] }}</span>@endif
                            @if($item['exam_year'])<span>{{ $item['exam_year'] }}</span>@endif
                        </div>

                        <p>{{ $item['statement_excerpt'] }}</p>

                        <a href="{{ $item['url'] }}">Ver questão e alternativas →</a>
                    </article>
                @empty
                    <div class="catalog-empty">Nenhuma questão pública encontrada nesta classificação.</div>
                @endforelse
            </div>

            @if($questions->hasPages())
                <nav class="catalog-pagination" aria-label="Paginação">
                    @if($questions->onFirstPage())
                        <span class="is-disabled">← Anterior</span>
                    @else
                        <a href="{{ $questions->previousPageUrl() }}" rel="prev">← Anterior</a>
                    @endif

                    <span>Página {{ $questions->currentPage() }} de {{ $questions->lastPage() }}</span>

                    @if($questions->hasMorePages())
                        <a href="{{ $questions->nextPageUrl() }}" rel="next">Próxima →</a>
                    @else
                        <span class="is-disabled">Próxima →</span>
                    @endif
                </nav>
            @endif
        </main>

        <aside class="catalog-sidebar">
            <div class="catalog-side-card">
                <span class="catalog-eyebrow">Papirar</span>
                <h2>Quer responder e conferir o gabarito?</h2>
                <p>Abra uma questão, crie sua conta e continue estudando sem perder o contexto.</p>
                <a href="{{ route('site.questions.index') }}" class="btn btn-outline full">Explorar disciplinas</a>
            </div>
        </aside>
    </div>
</section>
@endsection
