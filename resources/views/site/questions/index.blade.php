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
<section class="catalog-hero catalog-hero-compact">
    <div class="site-container">
        <h1>Pratique questões por assunto</h1>
        <p>Escolha uma disciplina, selecione o tópico e comece a responder.</p>
    </div>
</section>

<section class="catalog-section">
    <div class="site-container">
        <div class="catalog-heading">
            <h2>Escolha uma disciplina</h2>
            <p>Toque na disciplina para ver os tópicos disponíveis.</p>
        </div>

        <div class="subject-accordion" data-subject-accordion>
            @foreach($subjects as $subject)
                @php($panelId = 'subject-topics-' . $subject->id)
                <section class="subject-accordion-item">
                    <button
                        type="button"
                        class="subject-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="{{ $panelId }}"
                    >
                        <span>{{ $subject->name }}</span>
                        <span class="subject-accordion-icon" aria-hidden="true">+</span>
                    </button>

                    <div class="subject-topic-panel" id="{{ $panelId }}" hidden>
                        <p>Selecione um tópico para começar:</p>
                        <div class="subject-topic-links">
                            @foreach($subject->topics as $topic)
                                @if($topic->latestPublicQuestion)
                                    <a href="{{ \App\Support\PublicQuestionUrl::url($topic->latestPublicQuestion) }}">
                                        {{ $topic->name }}
                                        <span aria-hidden="true">→</span>
                                    </a>
                                @endif
                            @endforeach

                            @if($subject->latestPublicQuestion)
                                <a href="{{ \App\Support\PublicQuestionUrl::url($subject->latestPublicQuestion) }}" class="subject-all-link">
                                    Começar por qualquer tópico
                                    <span aria-hidden="true">→</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.subject-accordion-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = document.getElementById(button.getAttribute('aria-controls'));
            const accordion = button.closest('[data-subject-accordion]');
            const willOpen = button.getAttribute('aria-expanded') !== 'true';

            accordion.querySelectorAll('.subject-accordion-trigger').forEach(function (otherButton) {
                otherButton.setAttribute('aria-expanded', 'false');
                const otherPanel = document.getElementById(otherButton.getAttribute('aria-controls'));
                if (otherPanel) otherPanel.hidden = true;
            });

            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (panel) panel.hidden = !willOpen;
        });
    });
});
</script>
@endpush
