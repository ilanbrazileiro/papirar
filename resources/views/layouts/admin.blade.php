<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin | Papirar')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">

    <style>
        .content-wrapper { background: #f4f6f9; }
        .ck-editor__editable_inline { min-height: 240px; }
        .ck-content img { max-width: 100%; height: auto; }
        .ck-content figure.image { margin: 1rem auto; }
        .ck-content figure.image-style-side { max-width: 50%; }
        .papirar-admin-form .form-group label { font-weight: 600; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-editor.js'])
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Site
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Sair</button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin.dashboard') }}" class="brand-link text-center">
            <span class="brand-text font-weight-bold">Papirar Admin</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-header">Cadastros</li>
                    <li class="nav-item"><a href="{{ route('admin.corporations.index') }}" class="nav-link"><i class="nav-icon fas fa-shield-alt"></i><p>Corporações</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.exams.index') }}" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Concursos</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.subjects.index') }}" class="nav-link"><i class="nav-icon fas fa-book"></i><p>Disciplinas</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.topics.index') }}" class="nav-link"><i class="nav-icon fas fa-tags"></i><p>Tópicos</p></a></li>

                    <li class="nav-header">Questões</li>
                    <li class="nav-item"><a href="{{ route('admin.questions.index') }}" class="nav-link"><i class="nav-icon fas fa-question-circle"></i><p>Questões</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.questions.import.create') }}" class="nav-link"><i class="nav-icon fas fa-file-import"></i><p>Importar questões</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.comments.index') }}" class="nav-link"><i class="nav-icon fas fa-comments"></i><p>Comentários</p></a></li>

                    <li class="nav-header">Comercial</li>
                    <li class="nav-item"><a href="{{ route('admin.plans.index') }}" class="nav-link"><i class="nav-icon fas fa-credit-card"></i><p>Planos</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.customers.index') }}" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Clientes</p></a></li>
                    <li class="nav-item"><a href="{{ route('admin.subscriptions.index') }}" class="nav-link"><i class="nav-icon fas fa-receipt"></i><p>Assinaturas</p></a></li>

                    <li class="nav-header">Suporte</li>
                    <li class="nav-item"><a href="{{ route('admin.tickets.index') }}" class="nav-link"><i class="nav-icon fas fa-headset"></i><p>Tickets</p></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0">@yield('title', 'Admin')</h1>
                </div>
                <div>
                    @yield('page_actions')
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Verifique os campos abaixo.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Papirar</strong> &copy; {{ date('Y') }}.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    window.PAPIRAR_EDITOR_UPLOAD_URL = "{{ route('admin.editor-images.upload') }}";
</script>
@stack('scripts')
</body>
</html>
