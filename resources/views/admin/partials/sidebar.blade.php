<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('/admin/dashboard') }}" class="brand-link logo-switch">
        <img src="{{ asset('images/papirar-logo.png') }}" alt="Papirar" class="brand-image img-circle logo-xl">
        
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-gauge-high"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">CONTEÚDO</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/questions') }}" class="nav-link {{ request()->is('admin/questions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-circle-question"></i>
                        <p>Questões</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/questions/import') }}" class="nav-link {{ request()->is('admin/questions/import*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-import"></i>
                        <p>Importar questões</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/subjects') }}" class="nav-link {{ request()->is('admin/subjects*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Disciplinas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/topics') }}" class="nav-link {{ request()->is('admin/topics*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list-check"></i>
                        <p>Tópicos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/comentarios') }}" class="nav-link {{ request()->is('admin/comentarios*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-comments"></i>
                        <p>Comentários</p>
                    </a>
                </li>

                <li class="nav-header">CONCURSOS</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/corporations') }}" class="nav-link {{ request()->is('admin/corporations*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-shield-halved"></i>
                        <p>Corporações</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/admin/exams') }}" class="nav-link {{ request()->is('admin/exams*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-landmark"></i>
                        <p>Concursos / Provas</p>
                    </a>
                </li>

                <li class="nav-header">CLIENTES</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/customers') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Clientes</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ url('/admin/tickets') }}" class="nav-link {{ request()->is('admin/tickets*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-headset"></i>
                        <p>Suporte</p>
                    </a>
                </li>
                <li class="nav-header">FINANCEIRO</li>
                <li class="nav-item">
                    <a href="{{ url('/admin/plans') }}" class="nav-link {{ request()->is('admin/plans*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-credit-card"></i>
                        <p>Planos</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
