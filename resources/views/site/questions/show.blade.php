@extends('site.site_layout')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('robots', 'index, follow, max-image-preview:large')

@push('head')
<link rel="stylesheet" href="{{ asset('css/public-question.css') }}">
<link rel="stylesheet" href="{{ asset('css/questions-catalog.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<section class="public-question-page">
    <div class="site-container public-question-grid col-12">
        <main>
            <article class="question-card">
                <div class="question-meta">
                    @if($question->subject)<span>{{ $question->subject->name }}</span>@endif
                    @if($question->topic)<span>{{ $question->topic->name }}</span>@endif
                    @if($question->examBoard)<span>{{ $question->examBoard->name }}</span>@endif
                    @if($question->exam?->year)<span>{{ $question->exam->year }}</span>@endif
                </div>

                <section class="public-performance" aria-label="Seu rendimento nesta sequência">
                    <div>
                        <strong>{{ $publicAnsweredCount }}</strong>
                        <span>{{ $publicAnsweredCount === 1 ? 'questão respondida' : 'questões respondidas' }}</span>
                    </div>
                    <div>
                        <strong>{{ $publicAccuracy }}%</strong>
                        <span>de acertos</span>
                    </div>
                </section>

                <h1>Questão de {{ $question->subject?->name ?: 'Concurso' }}</h1>

                @if($question->source_reference)
                    <div class="question-reference">
                        <strong>Referência:</strong> {!! $question->source_reference !!}
                    </div>
                @elseif($question->exam)
                    <div class="question-reference">
                        <strong>Referência:</strong>
                        {{ $question->exam->title }}
                        @if($question->corporation) — {{ $question->corporation->name }} @endif
                        @if($question->exam->year) — {{ $question->exam->year }} @endif
                    </div>
                @endif

                <div class="question-statement">{!! $question->statement !!}</div>

                @if(!$showResult)
                    <form id="publicQuestionForm" method="POST" action="{{ route('site.questions.answer', [
                        'subjectSlug' => $question->subject?->slug ?: \Illuminate\Support\Str::slug($question->subject?->name ?: 'questoes'),
                        'question' => $question->id,
                        'questionSlug' => \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u',' ',strip_tags(html_entity_decode($question->statement, ENT_QUOTES | ENT_HTML5,'UTF-8')))),90,'')) ?: 'questao',
                    ]) }}" @guest data-guest-limit-reached="{{ $guestLimitReached ? '1' : '0' }}" @endguest>
                        @csrf
                        <div class="alternatives-list">
                            @foreach($alternatives as $alternative)
                                <label class="alternative-item alternative-clickable">
                                    <input type="radio" name="alternative_id" value="{{ $alternative['id'] }}" required>
                                    <span class="alternative-letter">{{ $alternative['letter'] }}</span>
                                    <span>{!! $alternative['text'] !!}</span>
                                </label>
                            @endforeach
                        </div>
                        <button class="btn btn-primary" type="submit">Responder questão</button>
                    </form>
                @else
                    <div class="alternatives-list">
                        @foreach($alternatives as $alternative)
                            @php
                                $stateClass = '';
                                if (($alternative['is_correct'] ?? false)) $stateClass = 'alternative-correct';
                                if ($alternative['is_selected'] && !($alternative['is_correct'] ?? false)) $stateClass = 'alternative-wrong';
                            @endphp
                            <div class="alternative-item {{ $stateClass }}">
                                <span class="alternative-letter">{{ $alternative['letter'] }}</span>
                                <div>{!! $alternative['text'] !!}</div>
                            </div>
                        @endforeach
                    </div>

                    <section class="answer-result {{ $answerWasCorrect ? 'answer-result-correct' : 'answer-result-wrong' }}">
                        <strong>{{ $answerWasCorrect ? 'Resposta correta.' : 'Resposta incorreta.' }}</strong>
                    </section>

                    @if($question->commented_answer)
                        <section class="commented-answer">
                            <span class="gate-kicker">Gabarito comentado</span>
                            <h2>Entenda a resolução</h2>
                            <div>{!! $question->commented_answer !!}</div>
                        </section>
                    @endif

                    @guest
                        @if($guestAnsweredCount >= $guestAnswerLimit)
                            <section class="question-conversion-cta">
                                <span class="gate-kicker">Continue praticando</span>
                                <h2>Cadastre-se grátis para continuar respondendo</h2>
                                <p>Você concluiu suas {{ $guestAnswerLimit }} questões gratuitas. Crie sua conta para continuar estudando e acompanhar seu desempenho.</p>
                                <div class="gate-actions">
                                    <button type="button" class="btn btn-primary js-open-auth-modal" data-mode="register">Criar conta e continuar</button>
                                    <button type="button" class="btn btn-outline js-open-auth-modal" data-mode="login">Já tenho conta</button>
                                </div>
                            </section>
                        @elseif($nextQuestion)
                            <div class="question-next-action">
                                <a href="{{ $nextQuestion['url'] }}" class="btn btn-primary">Responder próxima questão →</a>
                            </div>
                        @endif
                    @else
                        @if($nextQuestion)
                            <div class="question-next-action">
                                <a href="{{ $nextQuestion['url'] }}" class="btn btn-primary">Responder próxima questão →</a>
                            </div>
                        @endif
                    @endguest
                @endif

            </article>
        </main>
    </div>
</section>

@guest
<div class="auth-modal-backdrop" id="authModalBackdrop" hidden></div>
<div class="auth-modal" id="authModal" hidden role="dialog" aria-modal="true">
    <div class="auth-modal-card">
        <button type="button" class="auth-modal-close" id="authModalClose">×</button>

        <div class="auth-modal-tabs">
            <button type="button" class="auth-tab is-active" data-auth-tab="register">Criar conta</button>
            <button type="button" class="auth-tab" data-auth-tab="login">Entrar</button>
        </div>

        <div data-auth-panel="register">
            <h2>Cadastre-se grátis para continuar respondendo</h2>
            <p>Continue praticando e acompanhe seu desempenho no Papirar.</p>
            <form id="modalRegisterForm">
                <label>Nome<input type="text" name="name" required></label>
                <label>E-mail<input type="email" name="email" required></label>
                <label>Senha<input type="password" name="password" required></label>
                <label>Confirmar senha<input type="password" name="password_confirmation" required></label>
                <input type="text" name="seguranca" class="hp-field" tabindex="-1" autocomplete="off">
                <div class="auth-modal-errors" data-form-errors="register"></div>
                <button type="submit" class="btn btn-primary full">Criar conta e continuar</button>
            </form>
        </div>

        <div data-auth-panel="login" hidden>
            <h2>Entre para responder</h2>
            <form id="modalLoginForm">
                <label>E-mail<input type="email" name="email" required></label>
                <label>Senha<input type="password" name="password" required></label>
                <div class="auth-modal-errors" data-form-errors="login"></div>
                <button type="submit" class="btn btn-primary full">Entrar e responder</button>
            </form>
        </div>
    </div>
</div>
@endguest
@endsection

@push('scripts')
@guest
<script>
window.PapirarPublicQuestionAuth = {
    registerUrl: @json(route('site.questions.modal-register')),
    loginUrl: @json(route('site.questions.modal-login')),
    openOnLoad: @json($openAuthModal),
    gateReached: @json($guestLimitReached),
    continueUrl: @json($nextQuestion['url'] ?? $canonicalUrl)
};
</script>
<script src="{{ asset('js/public-question-auth.js') }}"></script>
@endguest
@endpush
