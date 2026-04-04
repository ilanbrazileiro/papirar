<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="/inicio" class="navbar-brand">
        <img src="{{ asset('assets/backend/dist/img/logo-papirar-temp-peq.jpg') }}" alt="Papirar.com.br" class="brand-image">
        <span class="brand-text">Papirar.com.br</span>
      </a>
      
      <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        
      <!-- Right navbar links -->
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
         
        <!-- MINHA CONTA -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <div class="img-circle">
              <i class="fas fa-user mr-2"></i>
            
            <span class="brand-text font-weight-light">{{ session('user.email') }}</span>
            </div>
          </a>

          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <a href="/minha-conta" class="dropdown-item">
              <i class="fas fa-user mr-2"></i> Minha Conta
            </a>
            <div class="dropdown-divider"></div>

            <a href="/meus-pedidos" class="dropdown-item">
              <i class="fas fa-dollar-sign mr-2"></i> Meus Pedidos e Pagamentos
            </a>
            <div class="dropdown-divider"></div>

            <a href="/inicio" class="dropdown-item">
              <i class="fas fa-book mr-2"></i> Questões
            </a>
            <div class="dropdown-divider"></div>

            <a href="/meus-simulados" class="dropdown-item">
              <i class="fas fa-pen mr-2"></i> Meus Simulados
            </a>
            <div class="dropdown-divider"></div>

            <a href="/minha-conta/estatistica" class="dropdown-item">
              <i class="fas fa-chart-bar mr-2"></i> Minhas Estatísticas
            </a>
            <div class="dropdown-divider"></div>

            <a href="/meus-cadernos" class="dropdown-item">
              <i class="fas fa-book-open mr-2"></i> Meus Cadernos
            </a>
            <div class="dropdown-divider"></div>

            <a href="/suporte" class="dropdown-item">
              <i class="fas fa-headset mr-2"></i> Suporte
                  <span class="float-right text-muted text-sm">
                    <i class="fas fa-comments"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                  </span>
            </a>
            <div class="dropdown-divider"></div>

            <a href="/logout" class="dropdown-item dropdown-footer"> <i class="fas fa-sign-out-alt mr-2"></i>Sair</a>
          </div>
        </li>


      </ul>
    </div>
  </nav>
