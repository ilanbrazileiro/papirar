<!DOCTYPE html>
<html lang="pt_BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>Papirar.com.br</title>

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="{{ asset('assets/backend/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/backend/dist/css/adminlte.min.css') }}">
  <!-- STYLE PERSONALIZADO DO PAPIRAR -->
  <link rel="stylesheet" href="{{ asset('assets/extras/css/papirar.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

    {{-- Fim do Header--}}

    @yield('content')
    
</body>
</div>
    {{-- FOOTER --}}

  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      Quanto mais você estuda, mais se aproxima dos seus sonhos!
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2020-<?= date('Y') ?> <a href="https://papirar.com.br">Papirar.com.br</a>.</strong> Todos os direitos reservados.
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="{{ asset('assets/backend/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/backend/dist/js/adminlte.min.js') }}"></script>


    {{-- Fim do FOOTER --}}