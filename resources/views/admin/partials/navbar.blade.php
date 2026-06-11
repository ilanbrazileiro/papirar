<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" data-lte-toggle="sidebar" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin</a>
        </li>
    </ul>

    <ul class="navbar-nav ms-auto ml-auto">
        @auth
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="adminAccountDropdown" role="button" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-1 mr-1"></i>
                    <span>{{ auth()->user()->name }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end dropdown-menu-right" aria-labelledby="adminAccountDropdown">
                    <a href="{{ route('admin.account.edit') }}" class="dropdown-item">
                        <i class="fas fa-user-cog me-2 mr-2"></i> Minha conta
                    </a>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2 mr-2"></i> Sair
                        </button>
                    </form>
                </div>
            </li>
        @endauth
    </ul>
</nav>
