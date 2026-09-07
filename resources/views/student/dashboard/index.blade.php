@extends('layouts.student')

@section('title', 'Dashboard do aluno')

@push('styles')
<style>
    .student-hero {
        background:
            radial-gradient(circle at top right, rgba(244, 197, 66, .30), transparent 20rem),
            linear-gradient(135deg, #0B1F3A 0%, #123B73 58%, #1557A8 100%);
        color: #fff;
        border-radius: 28px;
        padding: clamp(26px, 4vw, 46px);
        box-shadow: 0 24px 60px rgba(11, 31, 58, .22);
        overflow: hidden;
        position: relative;
    }

    .student-hero::after {
        content: '';
        position: absolute;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        right: -80px;
        bottom: -120px;
        background: rgba(244, 197, 66, .22);
    }

    .student-hero > * {
        position: relative;
        z-index: 1;
    }

    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 999px;
        padding: 7px 12px;
        color: #FDE68A;
        font-size: .78rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 14px;
    }

    .student-hero h1 {
        font-size: clamp(2rem, 4vw, 3.2rem);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
        margin-bottom: 16px;
        max-width: 820px;
    }

    .student-hero p {
        color: rgba(255,255,255,.82);
        max-width: 760px;
        font-size: 1.03rem;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .hero-mini-panel {
        background: rgba(255,255,255,.10);
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 22px;
        padding: 18px;
        backdrop-filter: blur(10px);
    }

    .hero-mini-panel .item {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        color: rgba(255,255,255,.82);
        padding: 9px 0;
        border-bottom: 1px solid rgba(255,255,255,.12);
    }

    .hero-mini-panel .item:last-child {
        border-bottom: 0;
    }

    .hero-mini-panel strong {
        color: #fff;
    }

    .dashboard-course-card {
        background: #fff;
        border: 1px solid var(--papirar-border);
        border-radius: 20px;
        padding: 18px;
        height: 100%;
        box-shadow: 0 10px 26px rgba(15, 35, 68, .05);
        transition: .16s ease;
    }

    .dashboard-course-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 38px rgba(15, 35, 68, .09);
    }

    .dashboard-side-card {
        background: #fff;
        border: 1px solid var(--papirar-border);
        border-radius: 18px;
        padding: 16px;
    }

    .feature-check {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-grid;
        place-items: center;
        background: #EAF8EF;
        color: #087F4F;
        font-weight: 900;
        font-size: .8rem;
        flex: 0 0 auto;
    }

    .continue-study-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 24px;
        align-items: center;
        padding: 24px;
        border: 1px solid rgba(244, 197, 66, .55);
        border-radius: 22px;
        background: linear-gradient(135deg, #fffdf6, #fff 58%, #f0f6ff);
        box-shadow: 0 14px 35px rgba(15, 35, 68, .08);
    }

    .continue-study-label {
        margin-bottom: 6px;
        color: #9A6B00;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .continue-study-card h2 {
        margin-bottom: 5px;
        color: var(--papirar-navy);
        font-size: clamp(1.35rem, 2.5vw, 1.8rem);
        font-weight: 900;
    }

    .continue-study-course {
        color: var(--papirar-blue);
        font-weight: 800;
    }

    .continue-study-context,
    .continue-study-progress {
        color: var(--papirar-muted);
        font-size: .9rem;
    }

    .continue-study-action .btn {
        min-width: 210px;
        padding: 13px 18px;
        font-weight: 900;
        box-shadow: 0 10px 24px rgba(244, 197, 66, .23);
    }

    .continue-study-arrow {
        display: inline-block;
        margin-left: 7px;
        animation: continueArrow 1.8s ease-in-out infinite;
    }

    @keyframes continueArrow {
        0%, 100% { transform: translateX(0); }
        50% { transform: translateX(5px); }
    }

    @media (prefers-reduced-motion: reduce) {
        .continue-study-arrow { animation: none; }
    }

    @media (max-width: 767.98px) {
        .continue-study-card { grid-template-columns: 1fr; gap: 16px; padding: 19px; }
        .continue-study-action .btn { width: 100%; min-width: 0; }
    }

    .today-panel { padding:24px; border-radius:24px; background:linear-gradient(135deg,#0f2344,#173b72); color:#fff; box-shadow:0 18px 45px rgba(15,35,68,.18); }
    .study-plan-today { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:20px; align-items:center; padding:22px; border:1px solid #cfe0f5; border-radius:22px; background:linear-gradient(135deg,#edf5ff,#fff); }
    .study-plan-progress { height:9px; overflow:hidden; border-radius:999px; background:#dce6f3; }
    .study-plan-progress span { display:block; height:100%; border-radius:inherit; background:#173b72; }
    @media(max-width:767.98px){.study-plan-today{grid-template-columns:1fr}.study-plan-today .btn{width:100%}}
    .today-panel-toggle { display:block; width:100%; padding:0; border:0; background:transparent; color:inherit; text-align:left; cursor:pointer; }
    .today-panel-head { display:flex; justify-content:space-between; gap:20px; align-items:center; }
    .today-panel-head h2 { margin:0; font-size:clamp(1.5rem,3vw,2rem); font-weight:950; }
    .today-panel-title { display:flex; align-items:center; gap:12px; }
    .today-panel-chevron { display:inline-block; color:#f4c542; font-size:1.35rem; transition:transform .2s ease; }
    .today-panel-toggle[aria-expanded="true"] .today-panel-chevron { transform:rotate(180deg); }
    .daily-missions-body { padding-top:18px; }
    .today-streaks { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
    .today-streaks span { padding:8px 10px; border:1px solid rgba(255,255,255,.16); border-radius:999px; background:rgba(255,255,255,.09); font-size:.78rem; font-weight:800; }
    .daily-mission-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .daily-mission { padding:16px; border:1px solid rgba(255,255,255,.14); border-radius:17px; background:rgba(255,255,255,.09); }
    .daily-mission.is-completed { border-color:rgba(74,222,128,.5); background:rgba(22,163,74,.18); }
    .daily-mission-top { display:flex; justify-content:space-between; gap:10px; }
    .daily-mission h3 { margin:0 0 4px; color:#fff; font-size:1rem; }
    .daily-mission p,.daily-mission small { color:#dbe7f7; }
    .daily-mission p { min-height:38px; margin:0 0 12px; font-size:.82rem; }
    .mission-check { color:#86efac; font-weight:900; }
    .mission-progress { height:8px; overflow:hidden; border-radius:999px; background:rgba(255,255,255,.13); }
    .mission-progress span { display:block; height:100%; border-radius:inherit; background:#f4c542; }
    .daily-mission.is-completed .mission-progress span { background:#4ade80; }
    .mission-progress-text { display:block; margin-top:8px; font-size:.74rem; font-weight:800; }
    @media(max-width:991.98px){.daily-mission-grid{grid-template-columns:1fr}.daily-mission p{min-height:0}}
    @media(max-width:767.98px){.today-panel{padding:18px}.today-panel-head{display:block}.today-streaks{justify-content:flex-start;margin-top:12px}.daily-mission{padding:14px}}
</style>
@endpush

@section('content')
    @if($trialLifecycle ?? null)
        @include('student.partials.trial-lifecycle-card', ['lifecycle' => $trialLifecycle])
    @endif

    @if($adaptiveRecommendation ?? null)
        @include('student.partials.adaptive-recommendation')
    @endif

    @if($needsEmailVerification ?? false)
        <div class="card-soft p-4 mb-4 border border-warning-subtle" style="background: linear-gradient(135deg, rgba(244, 197, 66, .22), rgba(255,255,255,1));">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="small text-uppercase fw-bold text-warning-emphasis mb-2">Confirmação de e-mail pendente</div>
                    <h2 class="h4 fw-bold mb-2">Confirme seu e-mail para validar sua conta.</h2>
                    <p class="mb-0 text-muted">
                        Verifique sua caixa de entrada e spam. Essa etapa aumenta a segurança da sua conta.
                    </p>
                </div>
                <form method="POST" action="{{ route('auth.verification.resend') }}">
                    @csrf
                    <button class="btn btn-warning">Reenviar confirmação</button>
                </form>
            </div>
        </div>
    @endif

    @if($todayPanel ?? null)
        <section class="today-panel mb-4">
            <button class="today-panel-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#daily-missions-body" aria-expanded="false" aria-controls="daily-missions-body">
                <div class="today-panel-head">
                    <div>
                        <div class="continue-study-label" style="color:#f4c542">Seu plano diário</div>
                        <div class="today-panel-title">
                            <h2>METAS DIÁRIAS</h2>
                            <span class="today-panel-chevron" aria-hidden="true">⌄</span>
                        </div>
                        <small>{{ $todayPanel['completed'] }}/3 metas concluídas · clique para visualizar</small>
                    </div>
                    <div class="today-streaks">
                        <span>🔥 {{ $todayPanel['streak']['current'] }} {{ $todayPanel['streak']['current'] === 1 ? 'dia seguido' : 'dias seguidos' }}</span>
                        <span>{{ $todayPanel['streak']['total_days'] }} {{ $todayPanel['streak']['total_days'] === 1 ? 'dia estudado' : 'dias estudados' }}</span>
                        <span>Melhor: {{ $todayPanel['streak']['best'] }} dias</span>
                    </div>
                </div>
            </button>
            <div class="collapse daily-missions-body" id="daily-missions-body" data-daily-missions-collapse>
                <div class="daily-mission-grid">
                    @foreach($todayPanel['missions'] as $mission)
                        <article class="daily-mission {{ $mission['completed'] ? 'is-completed' : '' }}" @if($mission['completed_now']) data-daily-mission-completed="{{ $mission['code'] }}" @endif>
                            <div class="daily-mission-top"><h3>{{ $mission['title'] }}</h3>@if($mission['completed'])<span class="mission-check">✓</span>@endif</div>
                            <p>{{ $mission['description'] }}</p>
                            <div class="mission-progress"><span style="width:{{ $mission['percent'] }}%"></span></div>
                            <small class="mission-progress-text">{{ $mission['progress_text'] }}</small>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($studyContinuation ?? null)
        <section class="continue-study-card mb-4" aria-labelledby="continue-study-title">
            <div>
                <div class="continue-study-label">Seu próximo passo</div>
                <h2 id="continue-study-title">{{ $studyContinuation['title'] }}</h2>
                <div class="continue-study-course">{{ $studyContinuation['course'] }}</div>
                <div class="continue-study-context">{{ $studyContinuation['context'] }}</div>
                <div class="continue-study-progress mt-2">{{ $studyContinuation['progress'] }}</div>
            </div>
            <div class="continue-study-action">
                <a
                    href="{{ $studyContinuation['url'] }}"
                    class="btn btn-warning"
                    data-continue-studying
                    data-continuation-type="{{ $studyContinuation['type'] }}"
                    data-course-id="{{ $studyContinuation['course_id'] }}"
                >
                    {{ $studyContinuation['button'] }}
                    <span class="continue-study-arrow" aria-hidden="true">→</span>
                </a>
            </div>
        </section>
    @endif

    @if($studyPlanToday)
        <section class="study-plan-today mb-4" data-study-plan-view data-course-id="{{ $studyPlanToday['plan']->course_id }}">
            <div>
                <div class="continue-study-label">Cronograma de estudos</div>
                <h2 class="h4 fw-bold mb-1">Plano de hoje · {{ $studyPlanToday['plan']->course->title }}</h2>
                @if($studyPlanToday['is_study_day'] && $studyPlanToday['subject'])
                    <div class="continue-study-course mb-2">{{ $studyPlanToday['subject']->name }} · {{ $studyPlanToday['answered'] }}/{{ $studyPlanToday['target'] }} questões</div>
                    <div class="study-plan-progress"><span style="width:{{ $studyPlanToday['percent'] }}%"></span></div>
                @elseif($studyPlanToday['next'] && $studyPlanToday['next']['subject'])
                    <div class="small-muted">Dia de descanso. Próximo estudo: {{ $studyPlanToday['next']['date']->translatedFormat('l, d/m') }} · {{ $studyPlanToday['next']['subject']->name }}.</div>
                @endif
            </div>
            <div>
                @if($studyPlanToday['is_study_day'] && $studyPlanToday['subject'] && !$studyPlanToday['completed'])
                    <form method="POST" action="{{ route('student.course-study.start', $studyPlanToday['plan']->course_id) }}" data-study-plan-start>
                        @csrf
                        <input type="hidden" name="subject_ids[]" value="{{ $studyPlanToday['subject']->id }}">
                        <input type="hidden" name="quantity" value="{{ $studyPlanToday['remaining'] }}">
                        <input type="hidden" name="mode" value="train">
                        <button class="btn btn-warning px-4">COMEÇAR AGORA →</button>
                    </form>
                @elseif($studyPlanToday['completed'])
                    <span class="badge text-bg-success p-3">Meta concluída ✓</span>
                @else
                    <a href="{{ route('student.study-plan.index') }}" class="btn btn-outline-primary">Ver cronograma</a>
                @endif
            </div>
        </section>
    @elseif(!($needsCourse ?? true))
        <section class="study-plan-today mb-4">
            <div><div class="continue-study-label">Organize sua preparação</div><h2 class="h4 fw-bold mb-1">Crie seu cronograma de estudos</h2><div class="small-muted">Defina seus dias e receba uma disciplina para estudar a cada sessão.</div></div>
            <a href="{{ route('student.study-plan.index') }}" class="btn btn-primary px-4">CRIAR CRONOGRAMA</a>
        </section>
    @endif

    @if(($pendingErrorsCount ?? 0) > 0 && ($reviewCourse ?? null))
        <section class="card-soft p-4 mb-4 border border-danger-subtle" data-error-review-view data-course-id="{{ $reviewCourse->id }}" data-pending-errors="{{ $pendingErrorsCount }}">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="small text-uppercase fw-bold text-danger mb-1">Revisão de erros</div>
                    <h2 class="h4 fw-bold mb-1">Você possui {{ $pendingErrorsCount }} {{ $pendingErrorsCount === 1 ? 'questão pendente' : 'questões pendentes' }}.</h2>
                    <div class="small-muted">Refaça seus erros mais recentes e acompanhe quantos já foram superados.</div>
                </div>
                <form method="POST" action="{{ route('student.course-study.start', $reviewCourse) }}" data-error-review-start>
                    @csrf
                    <input type="hidden" name="mode" value="review">
                    <input type="hidden" name="quantity" value="{{ min(10, $pendingErrorsCount) }}">
                    <button class="btn btn-danger px-4">Revisar agora →</button>
                </form>
            </div>
        </section>
    @endif

    <section class="student-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="hero-eyebrow">Papirar Concursos</div>

                @if($needsCourse ?? false)
                    <h1>Escolha seu curso e comece a treinar com direção.</h1>
                    <p class="mb-0">
                        O Papirar organiza sua preparação por curso, disciplinas, tópicos, questões comentadas e simulados. Comece pelo curso certo para o seu objetivo.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('student.subscriptions.index') }}" class="btn btn-warning">Ver cursos disponíveis</a>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-outline-light">Conhecer área do aluno</a>
                    </div>
                @else
                    <h1>Continue sua preparação pelo caminho certo.</h1>
                    <p class="mb-0">
                        Acesse seus cursos ativos, resolva questões, refaça favoritas e acompanhe sua evolução dentro do conteúdo comprado.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('student.courses.index') }}" class="btn btn-warning">Continuar estudando</a>
                        <a href="{{ route('student.subscriptions.index') }}" class="btn btn-outline-light">Renovar ou ampliar</a>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="hero-mini-panel">
                    <div class="fw-bold mb-2">Resumo do aluno</div>
                    <div class="item"><span>Cursos ativos</span><strong>{{ $stats['active_courses_count'] ?? 0 }}</strong></div>
                    <div class="item"><span>Questões respondidas</span><strong>{{ $stats['answers_count'] ?? 0 }}</strong></div>
                    <div class="item"><span>Aproveitamento</span><strong>{{ number_format((float) ($stats['accuracy'] ?? 0), 1, ',', '.') }}%</strong></div>
                    <div class="item"><span>Favoritas</span><strong>{{ $stats['favorites_count'] ?? 0 }}</strong></div>
                </div>
            </div>
        </div>
    </section>

    @if(($pendingTransactions ?? collect())->count())
        <div class="card-soft p-4 mb-4 border border-warning-subtle bg-warning-subtle bg-opacity-25">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="section-title mb-1">Pagamento pendente</div>
                    <div class="small-muted">Existe uma compra iniciada aguardando conclusão.</div>
                </div>
                <a href="{{ route('student.purchases.index') }}" class="btn btn-warning">Ver compras</a>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Cursos ativos</div>
                <div class="value">{{ $stats['active_courses_count'] ?? 0 }}</div>
                <div class="small-muted">acessos liberados</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Sessões de estudo</div>
                <div class="value">{{ $stats['study_sessions_count'] ?? 0 }}</div>
                <div class="small-muted">treinos iniciados</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Questões respondidas</div>
                <div class="value">{{ $stats['answers_count'] ?? 0 }}</div>
                <div class="small-muted">resoluções feitas</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="label">Simulados</div>
                <div class="value">{{ $stats['simulated_exams_count'] ?? 0 }}</div>
                <div class="small-muted">por curso</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 mb-4" id="meus-cursos">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <div>
                        <div class="section-title mb-1">Meus cursos</div>
                        <div class="small-muted">Acesse seus cursos ativos e continue estudando.</div>
                    </div>
                    <a href="{{ route('student.courses.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>

                @if(($activeCourseAccesses ?? collect())->count())
                    <div class="row g-3">
                        @foreach($activeCourseAccesses as $access)
                            @if($access->course)
                                <div class="col-md-6">
                                    <div class="dashboard-course-card">
                                        <div class="d-flex justify-content-between gap-3 mb-2">
                                            <div class="fw-bold text-dark">{{ $access->course->title }}</div>
                                            <span class="badge text-bg-success align-self-start">{{ $access->accessTypeLabel() }}</span>
                                        </div>

                                        <div class="small-muted mb-3">
                                            Acesso até: <strong>{{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</strong>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('student.courses.show', $access->course) }}" class="btn btn-sm btn-primary">Entrar</a>
                                            <a href="{{ route('student.courses.study', $access->course) }}" class="btn btn-sm btn-warning">Estudar</a>
                                            <a href="{{ route('student.subscriptions.index') }}" class="btn btn-sm btn-outline-secondary">Renovar</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="dashboard-course-card">
                        <div class="fw-bold mb-1">Você ainda não possui cursos ativos.</div>
                        <div class="small-muted mb-3">Escolha um curso para liberar treinos, simulados, comentários e desempenho.</div>
                        <a href="{{ route('student.subscriptions.index') }}" class="btn btn-primary">Ver cursos disponíveis</a>
                    </div>
                @endif
            </div>

            <div class="card-soft p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <div>
                        <div class="section-title mb-1">Simulados recentes</div>
                        <div class="small-muted">Últimas provas criadas dentro dos seus cursos.</div>
                    </div>
                </div>

                @if(($recentSimulatedExams ?? collect())->count())
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Curso</th>
                                    <th>Título</th>
                                    <th>Questões</th>
                                    <th>Acerto</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSimulatedExams as $exam)
                                    <tr>
                                        <td>{{ $exam->course->title ?? '-' }}</td>
                                        <td class="fw-semibold">{{ $exam->title }}</td>
                                        <td>{{ $exam->total_questions }}</td>
                                        <td>{{ number_format((float) $exam->accuracy, 2, ',', '.') }}%</td>
                                        <td>{{ $exam->finished_at ? $exam->finished_at->format('d/m/Y H:i') : 'Em andamento' }}</td>
                                        <td class="text-end">
                                            @if($exam->course)
                                                <a href="{{ route('student.courses.simulated.show', [$exam->course, $exam]) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="dashboard-course-card">
                        <div class="fw-bold mb-1">Nenhum simulado criado ainda.</div>
                        <div class="small-muted">Crie simulados dentro de um curso ativo para medir seu desempenho.</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Cursos disponíveis</div>

                @if(($recommendedCourses ?? collect())->count())
                    <div class="d-grid gap-3">
                        @foreach($recommendedCourses as $course)
                            <div class="dashboard-side-card">
                                <div class="fw-bold">{{ $course->title }}</div>
                                <div class="small-muted mb-2">{{ $course->short_description ?: $course->commercialHeadline() }}</div>

                                @if($course->price)
                                    <div class="small mb-3">A partir de <strong>R$ {{ number_format((float) $course->price, 2, ',', '.') }}</strong></div>
                                @endif

                                <a href="{{ route('student.subscriptions.index') }}" class="btn btn-sm btn-outline-primary w-100">Ver curso</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="small-muted">Nenhum curso disponível no momento.</div>
                @endif
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Como usar melhor</div>

                <div class="d-grid gap-3">
                    <div class="d-flex gap-2">
                        <span class="feature-check">✓</span>
                        <div>
                            <div class="fw-semibold">Estude por tópicos</div>
                            <div class="small-muted">Escolha exatamente o conteúdo que precisa revisar.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <span class="feature-check">✓</span>
                        <div>
                            <div class="fw-semibold">Use favoritas</div>
                            <div class="small-muted">Marque questões importantes e faça anotações.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <span class="feature-check">✓</span>
                        <div>
                            <div class="fw-semibold">Faça simulados</div>
                            <div class="small-muted">Treine tempo, atenção e desempenho por curso.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <span class="feature-check">✓</span>
                        <div>
                            <div class="fw-semibold">Volte aos erros</div>
                            <div class="small-muted">Use os resultados para revisar o que mais pesa.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
