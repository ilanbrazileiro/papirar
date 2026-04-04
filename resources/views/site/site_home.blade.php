@extends('site.site_layout')
@include('site.site_topbar')

@section('content')

<section class="pb_cover_v3 overflow-hidden cover-bg-indigo cover-bg-opacity text-left pb_gradient_v1 pb_slant-light" id="section-home">
  <div class="container">
    <div class="row align-items-center justify-content-center">
      <div class="col-md-6">
        <h2 class="heading mb-3">Estude através de simulados e questões</h2>
        <div class="sub-heading">
          <p class="mb-4">Cursos e concursos internos do CBMERJ E PMERJ (CHOAE, CAS, CFS, CFC). Questões preparadas para sua aprovação!               </p>
          <p class="mb-5"><a class="btn btn-success btn-lg pb_btn-pill smoothscroll" href="#" id="btnCadastrar"><span class="pb_font-14 text-uppercase pb_letter-spacing-1">Cadastre-se</span></a></p>
        </div>
      </div>
      <div class="col-md-1"></div>
        <div class="col-md-5 relative align-self-center">
            <form action="/" class="bg-white rounded pb_form_v1" method="post" id="fcadastrar" style="padding-top: 30px">
            <h2 class="mb-2 mt-0 text-center">Cadastre-se</h2>
            <div class="form-group">
                <input type="text" class="form-control pb_height-40 reverse" placeholder="Nome Completo" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control pb_height-40 reverse" placeholder="E-mail" id="email" name="email" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control pb_height-40 reverse" placeholder="Sua Senha" id="senha" name="senha" required>
            </div>

            <input type="text" name="seguranca" id="seguranca" style="visibility: hidden; display: none">

            <div class="form-group">
                <input type="password" class="form-control pb_height-40 reverse" placeholder="Confirme sua senha" id="c_senha" required>
            <small id="error" class="form-text text-danger" style="visibility: hidden;">Senhas não são iguais. Por favor tente novamente!</small>
            <?php if(isset($erro)){ ?>
            <small id="erro_email" class="form-text text-danger"><?= $erro ?></small>
            <?php } ?>
            </div>

            
            
            <div class="form-group">
                <input type="submit" class="btn btn-primary btn-lg btn-block pb_btn-pill  btn-shadow-blue" value="Cadastrar">
            </div>
            </form>
        </div>
    </div>
  </div>
</section>

@endsection