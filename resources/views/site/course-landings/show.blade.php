@extends('site.site_layout')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonicalUrl)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('body_class', 'site-page campaign-landing')

@push('head')
<link rel="stylesheet" href="{{ asset('css/course-landing.css') }}">
@include('site.course-landings._structured_data')
@endpush

@section('content')
@php
$ctaText=$course->landing_cta_text ?: ($course->is_trial_available ? 'Começar teste grátis' : 'Começar a estudar');
$finalCtaText=$course->landing_final_cta_text ?: $ctaText;
$ctaUrl=auth()->check() ? route('student.courses.index') : route('register',['course'=>$course->slug]);
$bullets=$course->salesBulletsList();
$billingCycles=$course->availableBillingCycles();
$showOrganization=(bool)$course->landing_show_schedule || (bool)$course->landing_show_goals;
@endphp

<section class="landing-hero"><div class="site-container landing-hero-grid"><div class="landing-hero-copy">
<span class="landing-kicker">Preparação direcionada · {{ $course->title }}</span>
<h1>{{ $course->landing_headline ?: 'Prepare-se para '.$course->title.' sabendo onde precisa melhorar.' }}</h1>
<p>{{ $course->landing_subheadline ?: 'Pratique questões e simulados, acompanhe seu desempenho, revise seus erros e use seus resultados para direcionar melhor os próximos estudos.' }}</p>
<div class="landing-actions"><a href="{{ $ctaUrl }}" class="btn btn-primary js-landing-cta" data-position="hero">{{ $ctaText }}</a>@if($demoQuestion)<a href="#experimente" class="btn landing-btn-light">Experimentar uma questão</a>@endif</div>
<div class="landing-proof"><span><strong>{{ number_format($totalQuestions,0,',','.') }}</strong> questões</span><span><strong>{{ $subjects->count() }}</strong> disciplinas</span><span><strong>{{ $topics->count() }}</strong> tópicos</span>@if($course->is_trial_available)<span><strong>{{ $course->trialDaysForAccess() }}</strong> dias grátis</span>@endif</div>
</div><aside class="landing-course-card">@if($course->coverImageUrl())<img src="{{ $course->coverImageUrl() }}" alt="{{ $course->title }}">@endif<div><small>Preparatório Papirar</small><h2>{{ $course->title }}</h2><strong>{{ $course->bestCommercialPriceLabel() }}</strong><a href="{{ $ctaUrl }}" class="btn btn-primary full js-landing-cta" data-position="hero_card">{{ $ctaText }}</a>@if($course->is_trial_available)<p>Teste por {{ $course->trialDaysForAccess() }} dias. Sem cobrança automática.</p>@endif</div></aside></div></section>

<section class="landing-section landing-problem"><div class="site-container landing-narrow"><span class="landing-kicker">Estudo com direção</span><h2>{{ $course->landing_problem_title ?: 'Você sabe quais conteúdos estão custando pontos na sua preparação?' }}</h2><p>{{ $course->landing_problem_text ?: 'Responder questões é mais útil quando cada resultado ajuda você a entender onde está evoluindo e onde ainda precisa concentrar seus estudos.' }}</p></div></section>

<section class="landing-section"><div class="site-container"><div class="landing-heading landing-heading-centered"><span class="landing-kicker">Como o Papirar ajuda</span><h2>Pratique. Descubra. Melhore.</h2><p>O ciclo de estudo do Papirar transforma prática em informação útil para orientar os próximos passos.</p></div><div class="landing-cycle"><article><b>1</b><h3>Pratique</h3><p>Resolva questões do escopo do curso e teste sua preparação com simulados.</p></article><div class="landing-cycle-arrow">→</div><article><b>2</b><h3>Descubra</h3><p>Acompanhe seus resultados e identifique conteúdos que merecem mais atenção.</p></article><div class="landing-cycle-arrow">→</div><article><b>3</b><h3>Melhore</h3><p>Revise erros e use seu desempenho para direcionar melhor os próximos estudos.</p></article></div></div></section>

@if($demoQuestion)
<section class="landing-section landing-demo" id="experimente"><div class="site-container landing-demo-grid"><div><span class="landing-kicker">Pratique agora</span><h2>Experimente uma questão real</h2><p>A demonstração usa o mesmo mecanismo público de questões do Papirar.</p></div><article class="landing-question-card js-landing-demo"><div class="landing-question-meta"><span>Questão #{{ $demoQuestion->id }}</span><span>{{ $demoQuestion->subject?->name }}</span>@if($demoQuestion->topic)<span>{{ $demoQuestion->topic->name }}</span>@endif</div>@if($demoQuestion->exam || $demoQuestion->examBoard)<small><strong>Referência:</strong> {{ $demoQuestion->exam?->title ?: 'Questão de concurso' }}@if($demoQuestion->examBoard) · {{ $demoQuestion->examBoard->name }}@endif</small>@endif<div class="landing-statement math-content">{!! $demoQuestion->statement !!}</div><form method="POST" class="js-landing-demo-form" action="{{ route('site.questions.answer',['subjectSlug'=>\App\Support\PublicQuestionUrl::subjectSlug($demoQuestion),'question'=>$demoQuestion->id,'questionSlug'=>\App\Support\PublicQuestionUrl::questionSlug($demoQuestion)]) }}">@csrf @foreach($demoQuestion->alternatives as $alternative)<label class="landing-alternative math-content"><input type="radio" name="alternative_id" value="{{ $alternative->id }}" required><b>{{ $alternative->letter }}</b><span>{!! $alternative->text !!}</span></label>@endforeach<button class="btn btn-primary" type="submit">Responder questão</button></form></article></div></section>
@endif

@if($course->landing_show_performance)
<section class="landing-section landing-feature"><div class="site-container landing-feature-grid"><div class="landing-feature-copy"><span class="landing-kicker">Diagnóstico</span><h2>Não estude no escuro.</h2><p>Seus resultados mostram onde sua preparação precisa de mais atenção. O desempenho ajuda você a enxergar acertos, erros e os conteúdos que merecem revisão.</p><ul class="landing-check-list"><li>Acompanhe seu aproveitamento.</li><li>Compare seu desempenho entre conteúdos estudados.</li><li>Use os resultados para decidir onde concentrar esforço.</li></ul></div><div class="landing-feature-visual">@if($course->landingPerformanceImageUrl())<img src="{{ $course->landingPerformanceImageUrl() }}" alt="Tela de desempenho do Papirar" loading="lazy">@else<div class="landing-ui-preview"><strong>Seu desempenho</strong><div class="landing-ui-preview-stats"><span><small>Questões</small><b>—</b></span><span><small>Acertos</small><b>—</b></span><span><small>Erros</small><b>—</b></span></div></div>@endif</div></div></section>
@endif

@if($course->landing_show_error_review)
<section class="landing-section landing-soft landing-feature"><div class="site-container landing-feature-grid"><div class="landing-review-card"><span class="landing-kicker">Revisão</span><strong>Errou uma questão?</strong><p>Ela pode voltar para sua revisão enquanto ainda precisa de atenção.</p><div>Revisar erros →</div></div><div class="landing-feature-copy"><span class="landing-kicker">Revisão de erros</span><h2>Transforme seus erros em próximos passos.</h2><p>Em vez de simplesmente seguir adiante, volte ao que causou dificuldade e fortaleça os pontos que ainda precisam de atenção.</p></div></div></section>
@endif

@if($course->landing_show_next_study)
<section class="landing-section landing-feature"><div class="site-container landing-feature-grid"><div class="landing-feature-copy"><span class="landing-kicker">Próximo estudo</span><h2>Use seu desempenho para escolher melhor onde estudar.</h2><p>O Papirar utiliza seu histórico de estudo para ajudar a destacar onde concentrar os próximos esforços dentro do curso.</p><p class="landing-note">A recomendação orienta a próxima ação de estudo; ela não substitui seu planejamento nem promete resultado em prova.</p></div><div class="landing-direction-card"><small>Próximo estudo sugerido</small><strong>Continue de onde seu desempenho pede mais atenção.</strong><span>Praticar → revisar → avançar</span></div></div></section>
@endif

@if($showOrganization)
<section class="landing-section landing-soft"><div class="site-container"><div class="landing-heading"><span class="landing-kicker">Organização e constância</span><h2>Saiba o que estudar hoje e transforme planejamento em constância.</h2></div><div class="landing-organization-grid">@if($course->landing_show_schedule)<article><span>01</span><h3>Cronograma</h3><p>Organize os estudos ao longo da semana e visualize o que precisa ser feito.</p></article>@endif @if($course->landing_show_goals)<article><span>02</span><h3>Metas</h3><p>Acompanhe objetivos de estudo e mantenha uma rotina mais consistente.</p></article>@endif</div></div></section>
@endif

@if($course->landing_show_simulations)
<section class="landing-section"><div class="site-container landing-feature-grid"><div class="landing-feature-copy"><span class="landing-kicker">Simulados</span><h2>Teste sua preparação antes do dia da prova.</h2><p>Monte simulados dentro do escopo do curso, responda sob tempo controlado e confira o resultado ao final.</p></div><div class="landing-simulation-card"><small>Simulado</small><strong>Questões do seu curso</strong><div class="landing-simulation-progress"><span></span></div><div class="landing-simulation-meta"><span>Progresso</span><span>Tempo</span><span>Resultado</span></div></div></div></section>
@endif

@if($subjects->isNotEmpty())
<section class="landing-section landing-soft"><div class="site-container"><div class="landing-heading"><span class="landing-kicker">Conteúdo real</span><h2>Disciplinas do curso</h2><p>O escopo é carregado a partir da configuração real do curso no Papirar.</p></div><div class="landing-subjects">@foreach($subjects as $subject)<a href="{{ route('site.questions.subject',['subjectSlug'=>$subject->slug]) }}"><strong>{{ $subject->name }}</strong><span>{{ number_format((int)$subject->landing_questions_count,0,',','.') }} questões disponíveis</span></a>@endforeach</div></div></section>
@endif

<section class="landing-section landing-offer js-landing-offer" id="oferta"><div class="site-container"><div class="landing-heading landing-heading-centered"><span class="landing-kicker">Acesso ao curso</span><h2>Comece sua preparação no Papirar.</h2><p>Escolha o período disponível para {{ $course->title }} e tenha acesso aos recursos configurados para este curso.</p></div><div class="landing-offer-card"><div class="landing-offer-main"><div><small>{{ $course->title }}</small><h3>{{ $course->commercialHeadline() }}</h3></div>@if($course->is_trial_available)<div class="landing-trial-badge">{{ $course->trialDaysForAccess() }} dias de teste grátis</div>@endif</div>@if(count($billingCycles))<div class="landing-pricing-grid">@foreach($billingCycles as $cycle=>$label)<div class="landing-price-card"><span>{{ $label }}</span><strong>R$ {{ number_format($course->priceForBillingCycle($cycle),2,',','.') }}</strong><small>{{ $course->periodDaysForBillingCycle($cycle) }} dias de acesso</small></div>@endforeach</div>@endif @if(count($bullets))<ul class="landing-offer-benefits">@foreach($bullets as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>@endif<div class="landing-offer-action"><a href="{{ $ctaUrl }}" class="btn btn-primary js-landing-cta" data-position="offer">{{ $ctaText }}</a>@if($course->guarantee_text)<small>{{ $course->guarantee_text }}</small>@elseif($course->is_trial_available)<small>O teste grátis não gera cobrança automática.</small>@endif</div></div></div></section>

@if($course->landingFaqs->isNotEmpty())
<section class="landing-section landing-faq"><div class="site-container landing-narrow-wide"><div class="landing-heading landing-heading-centered"><span class="landing-kicker">Dúvidas frequentes</span><h2>Antes de começar</h2></div><div class="landing-faq-list">@foreach($course->landingFaqs as $faq)<details class="landing-faq-item js-landing-faq" data-faq-id="{{ $faq->id }}"><summary>{{ $faq->question }}</summary><div class="landing-faq-answer">{!! nl2br(e($faq->answer)) !!}</div></details>@endforeach</div></div></section>
@endif

<section class="landing-section landing-final"><div class="site-container"><span class="landing-kicker">Próximo passo</span><h2>{{ $course->landing_final_title ?: 'Comece sua preparação para '.$course->title.' com mais direção.' }}</h2><p>{{ $course->landing_final_text ?: 'Pratique, acompanhe seus resultados e use o Papirar para identificar onde concentrar seus próximos estudos.' }}</p><a href="{{ $ctaUrl }}" class="btn btn-primary js-landing-cta" data-position="final">{{ $finalCtaText }}</a></div></section>
@endsection

@push('scripts')
<script>window.PapirarCourseLanding=@json(['course_id'=>(int)$course->id,'course_slug'=>$course->slug,'landing_page'=>$course->slug]);</script>
<script src="{{ asset('js/course-landing-tracking.js') }}" defer></script>
@endpush
