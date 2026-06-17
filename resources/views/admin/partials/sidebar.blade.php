@php
    $user = auth()->user();
    $isContent = ($user->role ?? null) === 'content';
    $questionMenuOpen = request()->routeIs('admin.questions.*')
        || request()->routeIs('admin.question-import-batches.*')
        || request()->is('admin/questions*');
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ $isContent && Route::has('admin.content.dashboard') ? route('admin.content.dashboard') : route('admin.dashboard') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">Papirar Admin</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @if($isContent)
                    @if(Route::has('admin.content.dashboard'))
                        <li class="nav-item">
                            <a href="{{ route('admin.content.dashboard') }}" class="nav-link {{ request()->routeIs('admin.content.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard de Conteúdo</p>
                            </a>
                        </li>
                    @endif
                @else
                    <li class="nav-header">DASHBOARDS</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard Geral</p>
                        </a>
                    </li>

                    @if(Route::has('admin.content.dashboard'))
                        <li class="nav-item">
                            <a href="{{ route('admin.content.dashboard') }}" class="nav-link {{ request()->routeIs('admin.content.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Dashboard de Conteúdo</p>
                            </a>
                        </li>
                    @endif
                @endif

                <li class="nav-header">CONTEÚDO</li>

                @if(Route::has('admin.questions.index'))
                    <li class="nav-item {{ $questionMenuOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $questionMenuOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-question-circle"></i>
                            <p>
                                Questões
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @if(Route::has('admin.questions.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.questions.create') }}" class="nav-link {{ request()->routeIs('admin.questions.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Adicionar</p>
                                    </a>
                                </li>
                            @endif

                            @if(Route::has('admin.questions.drafts'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.questions.drafts') }}" class="nav-link {{ request()->routeIs('admin.questions.drafts') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rascunhos</p>
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a href="{{ route('admin.questions.index', ['status' => 'draft']) }}" class="nav-link {{ request('status') === 'draft' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rascunhos</p>
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a href="{{ route('admin.question-video-lessons.index') }}" class="nav-link {{ request()->routeIs('admin.question-video-lessons.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-video"></i>
                                    <p>Aulas por questão</p>
                                </a>
                            </li>

                            @if(Route::has('admin.questions.import.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.questions.import.create') }}" class="nav-link {{ request()->routeIs('admin.questions.import.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Importar</p>
                                    </a>
                                </li>
                            @endif

                            @if(Route::has('admin.question-import-batches.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.question-import-batches.index') }}" class="nav-link {{ request()->routeIs('admin.question-import-batches.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Importações</p>
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.index') && request('status') !== 'draft' ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Todas</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if(Route::has('admin.subjects.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Disciplinas</p>
                        </a>
                    </li>
                @endif

                @if(Route::has('admin.topics.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.topics.index') }}" class="nav-link {{ request()->routeIs('admin.topics.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>Tópicos</p>
                        </a>
                    </li>
                @endif

                @if(Route::has('admin.planned-exams.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.planned-exams.index') }}" class="nav-link {{ request()->routeIs('admin.planned-exams.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Concursos planejados</p>
                        </a>
                    </li>
                @endif

                @if(Route::has('admin.exams.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.exams.index') }}" class="nav-link {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Concursos / Provas</p>
                        </a>
                    </li>
                @endif

                @if(Route::has('admin.source-materials.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.source-materials.index') }}" class="nav-link {{ request()->routeIs('admin.source-materials.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-folder-open"></i>
                            <p>Fontes e bibliografias</p>
                        </a>
                    </li>
                @endif

                @if(Route::has('admin.corporations.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.corporations.index') }}" class="nav-link {{ request()->routeIs('admin.corporations.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            <p>Corporações</p>
                        </a>
                    </li>
                @endif

                <li class="nav-header">SUPORTE</li>
                @if(Route::has('admin.tickets.index'))
                    <li class="nav-item">
                        <a href="{{ route('admin.tickets.index') }}" class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-headset"></i>
                            <p>Suporte</p>
                        </a>
                    </li>
                @endif

                @unless($isContent)
                    <li class="nav-header">COMERCIAL</li>
                    @if(Route::has('admin.courses.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-graduation-cap"></i>
                                <p>Cursos/Produtos</p>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('admin.course-accesses.index') }}" class="nav-link {{ request()->routeIs('admin.course-accesses.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-check"></i>
                            <p>Acessos a cursos</p>
                        </a>
                    </li>
                    @if(Route::has('admin.plans.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.plans.index') }}" class="nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tags"></i>
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

                    @if(Route::has('admin.subscriptions.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-credit-card"></i>
                                <p>Assinaturas</p>
                            </a>
                        </li>
                    @endif

                    @if(Route::has('admin.collaborators.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.collaborators.index') }}" class="nav-link {{ request()->routeIs('admin.collaborators.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Colaboradores</p>
                            </a>
                        </li>
                    @endif
                @endunless
            </ul>
        </nav>
    </div>
</aside>
