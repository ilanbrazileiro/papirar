@extends('layouts.student')

@section('title', 'Dashboard do aluno')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Olá, {{ auth()->user()->name ?? 'Aluno' }}</h1>
            <p class="page-subtitle">Resumo rápido da sua conta, dos seus cursos e do seu desempenho.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.index') }}" class="btn btn-primary">Meus cursos</a>
            <a href="{{ route('student.simulated.index') }}" class="btn btn-outline-primary">Meus simulados</a>
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

    @if($needsSubscription ?? false)
        <div class="alert alert-warning mb-4" role="alert">
            <strong>Nenhum curso ativo encontrado.</strong><br>
            Seu cadastro já está ativo para login, mas você precisa contratar ou receber acesso a um curso para começar a estudar.
            <div class="mt-3">
                <a href="{{ route('student.subscriptions.index') }}" class="btn btn-warning">Ver planos</a>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Cursos ativos</div><div class="value">{{ $stats['active_courses_count'] ?? 0 }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Sessões de estudo</div><div class="value">{{ $stats['study_sessions_count'] ?? 0 }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Questões respondidas</div><div class="value">{{ $stats['answers_count'] ?? 0 }}</div></div></div>
        <div class="col-md-6 col-xl-3"><div class="stats-card"><div class="label">Respostas corretas</div><div class="value">{{ $stats['correct_answers_count'] ?? 0 }}</div></div></div>
    </div>

    <div class="card-soft p-4 mb-4 border border-primary-subtle bg-primary-subtle bg-opacity-10">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="section-title mb-2">Estudo por curso</div>
                <div class="fw-semibold mb-1">Entre no curso que você assinou e estude somente as questões daquele escopo.</div>
                <div class="small-muted">
                    O Papirar agora organiza o acesso por cursos/produtos. Cada curso libera disciplinas, tópicos e fontes próprias.
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('student.courses.index') }}" class="btn btn-primary w-100 w-lg-auto">Abrir meus cursos</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Cursos ativos</div>

                @if(($activeCourseAccesses ?? collect())->count())
                    <div class="row g-3">
                        @foreach($activeCourseAccesses as $access)
                            @if($access->course)
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 bg-white h-100">
                                        <div class="fw-semibold">{{ $access->course->title }}</div>
                                        <div class="small-muted mb-3">Acesso até: {{ $access->ends_at ? $access->ends_at->format('d/m/Y') : 'Sem limite' }}</div>
                                        <a href="{{ route('student.courses.show', $access->course) }}" class="btn btn-sm btn-outline-primary">Entrar</a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="small-muted">Você ainda não possui cursos ativos.</div>
                @endif
            </div>

            <div class="card-soft p-4 h-100">
                <div class="section-title">Simulados recentes</div>

                @if(($recentSimulatedExams ?? collect())->count())
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr><th>Título</th><th>Questões</th><th>Acerto</th><th>Encerrado</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($recentSimulatedExams as $exam)
                                    <tr>
                                        <td class="fw-semibold">{{ $exam->title }}</td>
                                        <td>{{ $exam->total_questions }}</td>
                                        <td>{{ number_format((float) $exam->accuracy, 2, ',', '.') }}%</td>
                                        <td>{{ $exam->finished_at ? $exam->finished_at->format('d/m/Y H:i') : 'Em andamento' }}</td>
                                        <td class="text-end"><a href="{{ route('student.simulated.show', $exam) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="small-muted">Você ainda não criou simulados.</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4 mb-4">
                <div class="section-title">Assinatura atual</div>

                @if($currentSubscription)
                    <div class="mb-2"><span class="badge text-bg-success text-uppercase">{{ $currentSubscription->status }}</span></div>
                    <div class="fw-semibold">{{ $currentSubscription->plan->name ?? 'Plano sem nome' }}</div>
                    <div class="small-muted mt-2">Expira em: {{ optional($currentSubscription->expires_at)->format('d/m/Y H:i') ?? 'Não definido' }}</div>
                @else
                    <div class="small-muted">Você não possui assinatura geral ativa. O acesso aos cursos liberados continua funcionando pelo período vigente.</div>
                @endif

                <a href="{{ route('student.subscriptions.index') }}" class="btn btn-outline-primary w-100 mt-3">Gerenciar assinatura</a>
            </div>

            <div class="card-soft p-4">
                <div class="section-title">Atalhos rápidos</div>
                <div class="d-grid gap-2">
                    <a href="{{ route('student.courses.index') }}" class="btn btn-light border">Meus cursos</a>
                    <a href="{{ route('student.simulated.index') }}" class="btn btn-light border">Novo simulado</a>
                    <a href="{{ route('student.tickets.create') }}" class="btn btn-light border">Abrir ticket</a>
                    <a href="{{ route('student.account.edit') }}" class="btn btn-light border">Atualizar meus dados</a>
                </div>
            </div>
        </div>
    </div>
@endsection
