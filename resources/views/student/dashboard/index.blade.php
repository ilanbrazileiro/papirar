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
</style>
@endpush

@section('content')
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
