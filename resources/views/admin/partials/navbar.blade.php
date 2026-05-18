<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Abrir menu">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/admin/dashboard') }}" class="nav-link">Admin</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link" target="_blank">Ver site</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Conta">
                <i class="far fa-user-circle"></i>
                <span class="d-none d-md-inline ml-1">{{ auth()->user()->name ?? 'Admin' }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ url('/admin/dashboard') }}" class="dropdown-item">
                    <i class="fas fa-gauge mr-2"></i> Dashboard
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
