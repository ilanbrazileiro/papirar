@extends('layouts.student')

@section('title', 'Dashboard do aluno')

@section('content')
    <div class="card-soft p-4 p-lg-5 mb-4 border border-primary-subtle" style="background: linear-gradient(135deg, rgba(13, 110, 253, .10), rgba(255, 255, 255, 1));">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="small text-uppercase fw-semibold text-primary mb-2">Papirar Concursos</div>
                <h1 class="page-title mb-2">Estude por curso, com foco no edital que importa.</h1>
                <p class="page-subtitle mb-3">
                    Acesse questões organizadas por curso, resolva treinos direcionados, crie simulados e acompanhe sua evolução até a aprovação.
                </p>

                @if($needsCourse ?? false)
                    <div class="alert alert-info mb-0">
                        <strong>Escolha um curso para começar.</strong><br>
                        Seu acesso ao Papirar é organizado por cursos. Veja os cursos disponíveis e escolha o melhor caminho para sua preparação.
                    </div>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary">Continuar estudando</a>
                        <a href="#meus-cursos" class="btn btn-outline-primary">Ver meus cursos</a>
                    </div>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Seu painel</div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>Cursos ativos</span><strong>{{ $stats['active_courses_count'] ?? 0 }}</strong></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>Questões respondidas</span><strong>{{ $stats['answers_count'] ?? 0 }}</strong></div>
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>Aproveitamento</span><strong>{{ number_format((float) ($stats['accuracy'] ?? 0), 1, ',', '.') }}%</strong></div>
                        <div class="d-flex justify-content-between py-2"><span>Favoritas</span><strong>{{ $stats['favorites_count'] ?? 0 }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($needsEmailVerification ?? false)
        <div class="alert alert-warning d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4" role="alert">
            <div>
                <strong>Confirme seu e-mail.</strong><br>
                Enviamos uma mensagem para sua caixa de entrada. Confirme seu endereço de e-mail para validar sua conta.
            </div>

            <form method="POST" action="{{ route('auth.verification.resend') }}">
                @csrf
                <button class="btn btn-outline-warning">Reenviar e-mail</button>
            </form>
        </div>
    @endif

    @if(($pendingTransactions ?? collect())->count())
        <div class="card-soft p-4 mb-4 border border-warning-subtle bg-warning-subtle bg-opacity-25">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <div class="section-title mb-1">Pagamento pendente</div>
                    <div class="small-muted">Você possui compra iniciada aguardando conclusão.</div>
                </div>
                <a href="{{ route('student.purchases.index') }}" class="btn btn-warning">Ver compras</a>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Cursos ativos</div><div class="value">{{ $stats['active_courses_count'] ?? 0 }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Sessões de estudo</div><div class="value">{{ $stats['study_sessions_count'] ?? 0 }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Questões respondidas</div><div class="value">{{ $stats['answers_count'] ?? 0 }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Simulados por curso</div><div class="value">{{ $stats['simulated_exams_count'] ?? 0 }}</div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 mb-4" id="meus-cursos">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="section-title mb-0">Meus cursos</div>
                        <div class="small-muted">Acesse seus cursos ativos e continue de onde parou.</div>
                    </div>
                    <a href="{{ route('student.courses.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>

                @if(($activeCourseAccesses ?? collect())->count())
                    <div class="row g-3">
                        @foreach($activeCourseAccesses as $access)
                            @if($access->course)
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 bg-white h-100">
                                        <div class="fw-semibold">{{ $access->course->title }}</div>
                                        <div class="small-muted mb-3">Acesso até: {{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('student.courses.show', $access->course) }}" class="btn btn-sm btn-primary">Entrar</a>
                                            <a href="{{ route('student.courses.study', $access->course) }}" class="btn btn-sm btn-outline-primary">Estudar</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="small-muted">Você ainda não possui cursos ativos.</div>
                @endif
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Simulados recentes</div>

                @if(($recentSimulatedExams ?? collect())->count())
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr><th>Curso</th><th>Título</th><th>Questões</th><th>Acerto</th><th>Encerrado</th><th></th></tr>
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
                    <div class="small-muted">Você ainda não criou simulados dentro dos cursos.</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Cursos disponíveis</div>
                @if(($recommendedCourses ?? collect())->count())
                    <div class="d-grid gap-3">
                        @foreach($recommendedCourses as $course)
                            <div class="border rounded-4 p-3 bg-white">
                                <div class="fw-semibold">{{ $course->title }}</div>
                                <div class="small-muted mb-2">{{ $course->short_description ?: 'Curso com questões direcionadas para sua preparação.' }}</div>
                                @if($course->price)
                                    <div class="small mb-3">A partir de <strong>R$ {{ number_format((float) $course->price, 2, ',', '.') }}</strong></div>
                                @endif
                                <a href="{{ route('student.courses.index') }}" class="btn btn-sm btn-outline-primary w-100">Conhecer curso</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="small-muted">Nenhum curso disponível no momento.</div>
                @endif
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Por que estudar pelo Papirar?</div>
                <ul class="list-clean mb-0">
                    <li class="py-2">Questões organizadas por curso e edital.</li>
                    <li class="py-2">Treino com disciplinas e tópicos específicos.</li>
                    <li class="py-2">Simulados dentro do escopo do curso.</li>
                    <li class="py-2">Favoritos para revisar questões importantes.</li>
                    <li class="py-2">Acompanhamento de desempenho por curso.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
