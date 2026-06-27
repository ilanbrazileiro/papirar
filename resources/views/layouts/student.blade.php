<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Papirar')</title>

    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">

    <style>
        :root {
            --papirar-navy: #0F2344;
            --papirar-blue: #173B72;
            --papirar-blue-soft: #EAF1FB;
            --papirar-yellow: #F4C542;
            --papirar-yellow-2: #FFE28A;
            --papirar-bg: #F3F6FB;
            --papirar-card: #FFFFFF;
            --papirar-border: #DFE7F3;
            --papirar-text: #122033;
            --papirar-muted: #66758A;
            --papirar-green: #059669;
            --papirar-red: #DC2626;
            --papirar-warning: #D97706;

            --brand: var(--papirar-blue);
            --brand-dark: var(--papirar-navy);
            --text: var(--papirar-text);
            --muted: var(--papirar-muted);
            --bg: var(--papirar-bg);
            --card: var(--papirar-card);
            --border: var(--papirar-border);
            --success: var(--papirar-green);
            --danger: var(--papirar-red);
            --warning: var(--papirar-warning);
        }

        * { box-sizing: border-box; }

        body {
            background:
                radial-gradient(circle at top left, rgba(244, 197, 66, .10), transparent 30rem),
                radial-gradient(circle at top right, rgba(23, 59, 114, .12), transparent 32rem),
                var(--papirar-bg);
            color: var(--papirar-text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
        }

        a { text-decoration: none; }

        .student-topbar {
            background:
                radial-gradient(circle at top right, rgba(244, 197, 66, .18), transparent 22rem),
                linear-gradient(135deg, var(--papirar-navy) 0%, var(--papirar-blue) 100%);
            border-bottom: 1px solid rgba(255, 255, 255, .10);
            box-shadow: 0 16px 38px rgba(15, 35, 68, .22);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .student-navbar-inner {
            max-width: 1280px;
            margin: 0 auto;
            min-height: 74px;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: .72rem;
            color: #fff;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .brand-mark:hover { color: #fff; }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            display: grid;
            place-items: center;
            color: var(--papirar-navy);
            background: linear-gradient(135deg, var(--papirar-yellow), var(--papirar-yellow-2));
            box-shadow: 0 10px 22px rgba(244, 197, 66, .28);
            font-weight: 950;
            font-size: 1.2rem;
        }

        .brand-name {
            display: block;
            line-height: 1;
            font-size: 1.18rem;
        }

        .brand-subtitle {
            display: block;
            font-size: .70rem;
            color: rgba(255, 255, 255, .72);
            font-weight: 800;
            letter-spacing: .075em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .student-topbar .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 12px;
            padding: .45rem .6rem;
        }

        .student-topbar .navbar-toggler:focus { box-shadow: 0 0 0 .2rem rgba(244, 197, 66, .20); }
        .student-topbar .navbar-toggler-icon { filter: invert(1) grayscale(1) brightness(2); }

        .student-topbar .nav-link {
            border-radius: 999px;
            color: rgba(255, 255, 255, .78);
            font-weight: 800;
            font-size: .93rem;
            padding: .58rem .92rem;
            transition: .16s ease;
        }

        .student-topbar .nav-link:hover {
            background: rgba(255, 255, 255, .10);
            color: #fff;
        }

        .student-topbar .nav-link.active {
            background: var(--papirar-yellow);
            color: var(--papirar-navy);
            box-shadow: 0 10px 22px rgba(244, 197, 66, .22);
        }

        .student-user-pill {
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .18);
            color: #fff;
            border-radius: 999px;
            padding: .42rem .48rem .42rem .82rem;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            max-width: 250px;
        }

        .student-user-pill:hover,
        .student-user-pill:focus {
            background: rgba(255, 255, 255, .16);
            color: #fff;
            border-color: rgba(255, 255, 255, .28);
        }

        .student-user-avatar {
            width: 31px;
            height: 31px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--papirar-yellow);
            color: var(--papirar-navy);
            font-size: .8rem;
            font-weight: 950;
            flex: 0 0 auto;
        }

        .student-topbar .dropdown-menu {
            border-radius: 18px;
            border: 1px solid rgba(15, 35, 68, .08);
            box-shadow: 0 18px 42px rgba(15, 35, 68, .18);
            padding: .55rem;
        }

        .student-topbar .dropdown-item {
            border-radius: 12px;
            font-weight: 700;
        }

        .student-topbar .dropdown-item:hover {
            background: var(--papirar-blue-soft);
            color: var(--papirar-navy);
        }

        .app-shell {
            max-width: 1220px;
            margin: 0 auto;
            padding: 30px 16px 64px;
        }

        .card-soft {
            background: rgba(255, 255, 255, .97);
            border: 1px solid var(--papirar-border);
            border-radius: 22px;
            box-shadow: 0 16px 42px rgba(15, 35, 68, .07);
        }

        .page-title {
            font-size: clamp(1.65rem, 2.5vw, 2.25rem);
            line-height: 1.08;
            font-weight: 900;
            color: var(--papirar-navy);
            margin-bottom: 6px;
            letter-spacing: -.035em;
        }

        .page-subtitle {
            color: var(--papirar-muted);
            margin-bottom: 0;
            line-height: 1.58;
        }

        .section-title {
            color: var(--papirar-navy);
            font-size: 1.08rem;
            font-weight: 850;
            margin-bottom: 14px;
        }

        .small-muted {
            color: var(--papirar-muted);
            font-size: .92rem;
        }

        .stats-card {
            background: #fff;
            border: 1px solid var(--papirar-border);
            border-radius: 20px;
            padding: 20px;
            height: 100%;
            box-shadow: 0 10px 28px rgba(15, 35, 68, .055);
            position: relative;
            overflow: hidden;
        }

        .stats-card::after {
            content: '';
            position: absolute;
            right: -24px;
            top: -24px;
            width: 74px;
            height: 74px;
            background: rgba(244, 197, 66, .20);
            border-radius: 50%;
        }

        .stats-card .label {
            color: var(--papirar-muted);
            font-size: .9rem;
            font-weight: 700;
        }

        .stats-card .value {
            color: var(--papirar-navy);
            font-size: 2rem;
            font-weight: 900;
            margin-top: 8px;
            letter-spacing: -.04em;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid var(--papirar-border);
            border-radius: 999px;
            background: #fff;
            color: var(--papirar-muted);
            font-size: .9rem;
            font-weight: 700;
        }

        .list-clean { list-style: none; padding-left: 0; margin-bottom: 0; }
        .list-clean li + li { border-top: 1px solid var(--papirar-border); }

        .question-card {
            background: #fff;
            border: 1px solid var(--papirar-border);
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 14px 36px rgba(15, 35, 68, .06);
        }

        .alt-card {
            border: 1px solid var(--papirar-border);
            border-radius: 16px;
            padding: 16px;
            background: #fff;
            transition: .15s ease;
            cursor: pointer;
        }

        .alt-card:hover {
            border-color: #B7C8DF;
            box-shadow: 0 8px 20px rgba(15, 35, 68, .06);
        }

        .alt-card.selected { border-color: var(--papirar-blue); background: #EEF5FF; }
        .alt-card.correct { border-color: #10B981; background: #ECFDF3; }
        .alt-card.wrong { border-color: #EF4444; background: #FEF2F2; }

        .ticket-bubble { border-radius: 16px; padding: 14px 16px; max-width: 760px; }
        .ticket-user { background: #EEF5FF; border: 1px solid #BFDBFE; }
        .ticket-admin { background: #F9FAFB; border: 1px solid var(--papirar-border); }

        .table thead th {
            color: var(--papirar-muted);
            font-weight: 800;
            border-bottom-width: 1px;
            font-size: .86rem;
            text-transform: uppercase;
            letter-spacing: .035em;
        }

        .btn-primary {
            --bs-btn-bg: var(--papirar-blue);
            --bs-btn-border-color: var(--papirar-blue);
            --bs-btn-hover-bg: var(--papirar-navy);
            --bs-btn-hover-border-color: var(--papirar-navy);
            --bs-btn-active-bg: var(--papirar-navy);
            --bs-btn-active-border-color: var(--papirar-navy);
            font-weight: 800;
            border-radius: 999px;
        }

        .btn-outline-primary {
            --bs-btn-color: var(--papirar-blue);
            --bs-btn-border-color: #AFC5E3;
            --bs-btn-hover-bg: var(--papirar-blue);
            --bs-btn-hover-border-color: var(--papirar-blue);
            font-weight: 800;
            border-radius: 999px;
        }

        .btn-warning {
            --bs-btn-bg: var(--papirar-yellow);
            --bs-btn-border-color: var(--papirar-yellow);
            --bs-btn-color: var(--papirar-navy);
            --bs-btn-hover-bg: #E8B826;
            --bs-btn-hover-border-color: #E8B826;
            --bs-btn-hover-color: var(--papirar-navy);
            font-weight: 900;
            border-radius: 999px;
        }

        .btn-outline-secondary, .btn-outline-danger { border-radius: 999px; font-weight: 800; }

        .form-control, .form-select {
            border-radius: 14px;
            border-color: var(--papirar-border);
            padding: .72rem .9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #9CB9DF;
            box-shadow: 0 0 0 .22rem rgba(23, 59, 114, .12);
        }

        .alert { border-radius: 18px; border-width: 1px; }

        .student-footer-note {
            color: var(--papirar-muted);
            font-size: .82rem;
            text-align: center;
            padding: 24px 0 0;
        }

        @media (max-width: 991px) {
            .student-navbar-inner { min-height: auto; }
            .app-shell { padding-top: 20px; }
            .student-topbar .navbar-collapse { padding: 1rem 0 .65rem; }
            .student-topbar .nav-link { border-radius: 14px; padding: .78rem .9rem; }
            .student-topbar .nav-link.active { box-shadow: none; }
            .student-user-pill { width: 100%; justify-content: space-between; margin-top: .55rem; border-radius: 14px; }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('assets/katex/katex.min.css') }}">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg student-topbar">
    <div class="container-fluid student-navbar-inner px-3 px-lg-4">
        {{--
        <a class="nav-brand brand-mark" href="{{ url('/') }}" aria-label="Papirar Concursos">
            <img src="{{ asset('images/logo-papirar.png') }}" alt="Papirar Concursos">
        </a>
        --}}
        <a class="navbar-brand brand-mark" href="{{ route('student.courses.index') }}">
            <span class="brand-icon">P</span>
            <span>
                <span class="brand-name">Papirar</span>
                <span class="brand-subtitle">Concursos militares</span>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav" aria-controls="studentNav" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="studentNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mt-3 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.courses.*') || request()->routeIs('student.course-study.*') ? 'active' : '' }}" href="{{ route('student.courses.index') }}">Meus cursos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.subscriptions.*') ? 'active' : '' }}" href="{{ route('student.subscriptions.index') }}">Assinatura</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.purchases.*') ? 'active' : '' }}" href="{{ route('student.purchases.index') }}">Compras</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.tickets.*') ? 'active' : '' }}" href="{{ route('student.tickets.index') }}">Suporte</a>
                </li>

                <li class="nav-item dropdown ms-lg-2">
                    <button class="btn student-user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-none d-xl-inline small fw-bold text-truncate" style="max-width: 160px;">
                            {{ auth()->user()->name ?? 'Aluno' }}
                        </span>
                        <span class="student-user-avatar">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end mt-2">
                        <li><a class="dropdown-item py-2" href="{{ route('student.account.edit') }}">Minha conta</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('student.subscriptions.index') }}">Assinatura</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('student.purchases.index') }}">Histórico de compras</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button class="dropdown-item py-2 text-danger">Sair</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="app-shell">
    @include('components.flash')

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Corrija os erros abaixo:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')

    <div class="student-footer-note">
        Papirar Concursos · estudo por questões, simulados e revisão direcionada.
    </div>
</main>

<script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/katex/katex.min.js') }}"></script>
<script src="{{ asset('assets/katex/contrib/auto-render.min.js') }}"></script>
<script src="{{ asset('js/papirar-katex.js') }}"></script>
@stack('scripts')
</body>
</html>
