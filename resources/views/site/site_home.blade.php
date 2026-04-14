@extends('site.site_layout')
@include('site.site_topbar')

@section('content')
<section class="pb_cover_v3 overflow-hidden cover-bg-indigo cover-bg-opacity text-left pb_gradient_v1 pb_slant-light" id="section-home">
  <div class="container">
    <div class="row align-items-center justify-content-center">
      <div class="col-md-6">
        @include('components.flash')
        <h2 class="heading mb-3">Estude através de simulados e questões</h2>
        <div class="sub-heading">
          <p class="mb-4">Cursos e concursos internos do CBMERJ e PMERJ. Questões preparadas para sua aprovação.</p>
          <p class="mb-5"><a class="btn btn-success btn-lg pb_btn-pill smoothscroll" href="#fcadastrar"><span class="pb_font-14 text-uppercase pb_letter-spacing-1">Cadastre-se</span></a></p>
        </div>
      </div>
      <div class="col-md-1"></div>
      <div class="col-md-5 relative align-self-center">
        <form action="{{ route('auth.register.store') }}" class="bg-white rounded pb_form_v1" method="post" id="fcadastrar" style="padding-top: 30px">
          @csrf
          <h2 class="mb-2 mt-0 text-center">Cadastre-se</h2>

          <div class="form-group">
            <input type="text" class="form-control pb_height-40 reverse" placeholder="Nome Completo" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
              <small class="form-text text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <input type="email" class="form-control pb_height-40 reverse" placeholder="E-mail" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
              <small class="form-text text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="form-group">
            <input type="password" class="form-control pb_height-40 reverse" placeholder="Sua Senha" id="password" name="password" required>
            @error('password')
              <small class="form-text text-danger">{{ $message }}</small>
            @enderror
          </div>

          <input type="text" name="seguranca" id="seguranca" style="visibility: hidden; display: none" tabindex="-1" autocomplete="off">

          <div class="form-group">
            <input type="password" class="form-control pb_height-40 reverse" placeholder="Confirme sua senha" id="password_confirmation" name="password_confirmation" required>
          </div>

          <div class="form-group">
            <input type="submit" class="btn btn-primary btn-lg btn-block pb_btn-pill btn-shadow-blue" value="Cadastrar">
          </div>

          <div class="text-center small text-muted mt-3">
            Você poderá entrar no sistema logo após o cadastro, mas precisará confirmar seu e-mail e contratar uma assinatura para responder questões.
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
