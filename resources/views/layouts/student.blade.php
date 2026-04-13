<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Papirar')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f62fe;
            --brand-dark: #0b4ecc;
            --text: #111827;
            --muted: #6b7280;
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e5e7eb;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
        }
        body { background: var(--bg); color: var(--text); font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .app-navbar { background: #fff; border-bottom: 1px solid var(--border); }
        .app-shell { max-width: 1180px; margin: 0 auto; padding: 28px 16px 56px; }
        .card-soft { background: var(--card); border: 1px solid var(--border); border-radius: 18px; box-shadow: 0 10px 30px rgba(15,23,42,.04); }
        .page-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 6px; }
        .page-subtitle { color: var(--muted); margin-bottom: 0; }
        .stats-card { background: #fff; border: 1px solid var(--border); border-radius: 18px; padding: 20px; height: 100%; }
        .stats-card .label { color: var(--muted); font-size: .95rem; }
        .stats-card .value { font-size: 1.85rem; font-weight: 800; margin-top: 8px; }
        .meta-badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--border); border-radius: 999px; background: #fff; color: var(--muted); font-size: .92rem; }
        .section-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 14px; }
        .list-clean { list-style: none; padding-left: 0; margin-bottom: 0; }
        .list-clean li + li { border-top: 1px solid var(--border); }
        .sidebar-link { color: #374151; text-decoration: none; display: block; padding: 10px 14px; border-radius: 12px; }
        .sidebar-link:hover, .sidebar-link.active { background: #eff6ff; color: var(--brand-dark); }
        .question-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 20px; }
        .alt-card { border: 1px solid var(--border); border-radius: 14px; padding: 16px; background: #fff; transition: .15s ease; cursor: pointer; }
        .alt-card:hover { border-color: #cbd5e1; box-shadow: 0 6px 18px rgba(15,23,42,.05); }
        .alt-card.selected { border-color: var(--brand); background: #eff6ff; }
        .alt-card.correct { border-color: #10b981; background: #ecfdf3; }
        .alt-card.wrong { border-color: #ef4444; background: #fef2f2; }
        .ticket-bubble { border-radius: 16px; padding: 14px 16px; max-width: 760px; }
        .ticket-user { background: #eff6ff; border: 1px solid #bfdbfe; }
        .ticket-admin { background: #f9fafb; border: 1px solid var(--border); }
        .small-muted { color: var(--muted); font-size: .92rem; }
        .table thead th { color: var(--muted); font-weight: 600; border-bottom-width: 1px; }
        @media (max-width: 991px) {
            .app-shell { padding-top: 20px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container-fluid" style="max-width: 1280px;">
        <a class="navbar-brand fw-bold" href="{{ route('student.dashboard') }}">Papirar</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="studentNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('student.study.index') }}">Estudar</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('student.simulated.index') }}">Simulados</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('student.subscriptions.index') }}">Assinatura</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('student.tickets.index') }}">Suporte</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('student.account.edit') }}">Minha conta</a></li>
                <li class="nav-item ms-lg-2">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">Sair</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="app-shell">
    @include('components.flash')

    @if($errors->any())
        <div class="alert alert-danger rounded-4">
            <div class="fw-semibold mb-2">Corrija os erros abaixo:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
