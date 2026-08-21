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
    <div class="site-container public-question-grid">
        <main>
            <article class="question-card">
                <div class="question-meta">
                    @if($question->subject)<span>{{ $question->subject->name }}</span>@endif
                    @if($question->topic)<span>{{ $question->topic->name }}</span>@endif
                    @if($question->examBoard)<span>{{ $question->examBoard->name }}</span>@endif
                    @if($question->exam?->year)<span>{{ $question->exam->year }}</span>@endif
                </div>

                <h1>Questão de {{ $question->subject?->name ?: 'Concurso' }}</h1>

                <div class="question-statement">{!! $question->statement !!}</div>

                @guest
                    <div class="alternatives-list">
                        @foreach($alternatives as $alternative)
                            <div class="alternative-item">
                                <span class="alternative-letter">{{ $alternative['letter'] }}</span>
                                <div>{!! $alternative['text'] !!}</div>
                            </div>
                        @endforeach
                    </div>

                    <section class="question-gate">
                        <span class="gate-kicker">Responda e veja a resolução</span>
                        <h2>Qual alternativa você marcaria?</h2>
                        <p>Crie sua conta gratuitamente para responder, conferir o gabarito e acessar o comentário explicativo.</p>
                        <div class="gate-actions">
                            <button type="button" class="btn btn-primary js-open-auth-modal" data-mode="register">Responder gratuitamente</button>
                            <button type="button" class="btn btn-outline js-open-auth-modal" data-mode="login">Já tenho conta</button>
                        </div>
                    </section>
                @else
                    @if(!$showResult)
                        <form method="POST" action="{{ route('site.questions.answer', [
                            'subjectSlug' => $question->subject?->slug ?: \Illuminate\Support\Str::slug($question->subject?->name ?: 'questoes'),
                            'question' => $question->id,
                            'questionSlug' => \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u',' ',strip_tags(html_entity_decode($question->statement, ENT_QUOTES | ENT_HTML5,'UTF-8')))),90,'')) ?: 'questao',
                        ]) }}">
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
                    @endif
                @endguest

                @if(!empty($relatedQuestions) && $relatedQuestions->isNotEmpty())
                <section class="related-questions">
                    <span class="gate-kicker">Continue praticando</span>
                    <h2>Questões relacionadas</h2>

                    <div class="question-list-public">
                        @foreach($relatedQuestions as $related)
                            <a href="{{ $related['url'] }}" class="related-question-link">
                                <strong>{{ $related['subject'] }}</strong>
                                @if($related['topic'])
                                    <span>{{ $related['topic'] }}</span>
                                @endif
                                <em>Ver questão →</em>
                            </a>
                        @endforeach
                    </div>
                </section>
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
            <h2>Crie sua conta gratuita</h2>
            <p>Você permanece nesta questão e responde logo após o cadastro.</p>
            <form id="modalRegisterForm">
                <label>Nome<input type="text" name="name" required></label>
                <label>E-mail<input type="email" name="email" required></label>
                <label>Senha<input type="password" name="password" required></label>
                <label>Confirmar senha<input type="password" name="password_confirmation" required></label>
                <input type="text" name="seguranca" class="hp-field" tabindex="-1" autocomplete="off">
                <div class="auth-modal-errors" data-form-errors="register"></div>
                <button type="submit" class="btn btn-primary full">Criar conta e responder</button>
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
    loginUrl: @json(route('site.questions.modal-login'))
};
</script>
<script src="{{ asset('js/public-question-auth.js') }}"></script>
@endguest
@endpush
