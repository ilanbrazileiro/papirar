<!DOCTYPE html>
<html lang="pt">
	<head>
		
        <title>Simualdos para Cursos e Concursos</title>

        <meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="Concursos e cursos militares para CBMERJ E PMERJ">
		<meta name="keywords" content="Simualdos para o CBMERJ e PMERJ">

        <link href="https://fonts.googleapis.com/css?family=Crimson+Text:400,400i,600|Montserrat:200,300,400" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/frontend/css/bootstrap/bootstrap.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/fonts/ionicons/css/ionicons.min.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/fonts/law-icons/font/flaticon.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/fonts/fontawesome/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/css/slick.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/css/slick-theme.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/css/helpers.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/css/style.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/frontend/css/landing-2.css')}}">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        
    </head>
    {{-- Fim do cabeçalho--}}
    <body data-spy="scroll" data-target="#pb-navbar" data-offset="200">

        @yield('content')

    {{--            Inicio do Footer       --}}
    </body>
    <footer class="pb_footer bg-light" role="contentinfo">
      <div class="container">
        <div class="row text-center">
          <div class="col">
            <ul class="list-inline">
              <li class="list-inline-item"><a href="#" class="p-2"><i class="fa fa-facebook"></i></a></li>
              <li class="list-inline-item"><a href="#" class="p-2"><i class="fa fa-twitter"></i></a></li>
              <li class="list-inline-item"><a href="#" class="p-2"><i class="fa fa-linkedin"></i></a></li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="col text-center">
            <p class="pb_font-14">&copy; 2020. Desenvolvido por . <br>  <a href="https://imm-tecnologia.com.br">IMM-Tecnologia</a></p>
          </div>
        </div>
      </div>
    </footer>

    <!-- loader -->
    <div id="pb_loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#1d82ff"/></svg></div>
    <script src="{{ asset('assets/frontend/js/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/popper.min.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/slick.min.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/jquery.mb.YTPlayer.min.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/jquery.waypoints.min.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/jquery.easing.1.3.js')}}"></script>
    <script src="{{ asset('assets/frontend/js/main.js')}}"></script>