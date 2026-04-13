<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Papirar')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fb
        }

        .shell {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 16px 48px
        }

        .card-soft {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .04)
        }

        .small-muted {
            color: #6b7280
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800
        }

        .sidebar a {
            text-decoration: none;
            color: #374151;
            display: block;
            padding: 10px 12px;
            border-radius: 12px
        }

        .sidebar a:hover {
            background: #eff6ff;
            color: #0f62fe
        }
    </style>
    @stack('styles')
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container-fluid" style="max-width:1280px">
            <a class="navbar-brand fw-bold"
                href="{{ route('admin.dashboard') }}">Papirar Admin</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.collaborators.edit', auth()->user()) }}">{{ auth()->user()->name }}</a></li>
                    <li class="nav-item ms-lg-2">
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Sair</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="shell">
        <div class="row g-4">
            <div class="col-lg-2">
                <div class="card-soft p-3 sidebar"><a href="{{ route('admin.dashboard') }}">Dashboard</a><a
                        href="{{ route('admin.questions.index') }}">Questões</a><a
                        href="{{ route('admin.comments.index') }}">Comentários</a><a
                        href="{{ route('admin.tickets.index') }}">Tickets</a><a
                        href="{{ route('admin.customers.index') }}">Clientes</a><a
                        href="{{ route('admin.corporations.index') }}">Corporações</a><a
                        href="{{ route('admin.exams.index') }}">Concursos</a><a
                        href="{{ route('admin.subjects.index') }}">Disciplinas</a><a
                        href="{{ route('admin.topics.index') }}">Assuntos</a><a
                        href="{{ route('admin.plans.index') }}">Planos</a><a
                        href="{{ route('admin.subscriptions.index') }}">Assinaturas</a><a
                        href="{{ route('admin.collaborators.index') }}">Colaboradores</a></div>
            </div>
            
            <div class="col-lg-10">
                @include('components.flash') @if ($errors->any())
                <div class="alert alert-danger rounded-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
