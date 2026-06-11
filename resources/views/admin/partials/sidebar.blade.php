<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <span class="brand-text font-weight-light ml-2"><strong>Papirar</strong> Admin</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">CONTEÚDO</li>
                <li class="nav-item">
                    <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-question-circle"></i>
                        <p>Questões</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.questions.import.create') }}" class="nav-link">
                        <i class="nav-icon fas fa-file-import"></i>
                        <p>Importar questões</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Disciplinas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.topics.index') }}" class="nav-link {{ request()->routeIs('admin.topics.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>Tópicos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.planned-exams.index') }}" class="nav-link {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Concursos / Provas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.source-materials.index') }}" class="nav-link {{ request()->routeIs('admin.source-materials.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Fontes e bibliografias</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.corporations.index') }}" class="nav-link {{ request()->routeIs('admin.corporations.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-shield-alt"></i>
                        <p>Corporações</p>
                    </a>
                </li>

                <li class="nav-header">COMERCIAL</li>
                @if(Route::has('admin.plans.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.plans.index') }}" class="nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p>Planos</p>
                        </a>
                    </li>
                @endif
                @if(Route::has('admin.customers.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Clientes</p>
                        </a>
                    </li>
                @endif

                <li class="nav-header">SUPORTE</li>
                <li class="nav-item">
                    <a href="{{ route('admin.tickets.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-headset"></i>
                        <p>Suporte</p>
                    </a>
                </li>
                @if(Route::has('admin.collaborators.index'))
                <li class="nav-header">ADMINISTRAÇÃO</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.collaborators.index') }}" class="nav-link {{ request()->routeIs('admin.collaborators.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Colaboradores</p>
                        </a>
                    </li>
                </li>
                
                <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-copy"></i>
              <p>
                Layout Options
                <i class="fas fa-angle-left right"></i>
                <span class="badge badge-info right">6</span>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
              <li class="nav-item">
                <a href="pages/layout/top-nav.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Top Navigation</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/top-nav-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Top Navigation + Sidebar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/boxed.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Boxed</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Sidebar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-topnav.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Navbar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-footer.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Footer</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/collapsed-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Collapsed Sidebar</p>
                </a>
              </li>
            </ul>
          </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
