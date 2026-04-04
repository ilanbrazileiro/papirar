@extends('site.site_layout')
@include('site.site_topbar')

@section('content')

<section class="pb_cover_v3 overflow-hidden cover-bg-indigo cover-bg-opacity text-left pb_gradient_v1 pb_slant-light" id="section-home">
      <div class="container">
        <div class="row align-items-center justify-content-center">
          
          <div class="col-md-1">
          </div>
          <div class="col-md-5 relative align-self-center">

            <form action="/forgout" class="bg-white rounded pb_form_v1" method="post" id="fforgout">
              <?php if (isset($success)){ ?>
                <h2 class="mb-4 mt-0 text-center text-success">Verifique seu e-mail e cadastre uma nova senha!</h2>

              <?php } else { ?>
                <h2 class="mb-4 mt-0 text-center">Digite seu e-mail</h2>
                
                <div class="form-group">
                  <input type="email" class="form-control pb_height-50 reverse" placeholder="E-mail" id="email" name="email" required>
                  <?php if(isset($erro)){ ?>
                  <small id="erro_email" class="form-text text-danger"><?= $erro ?></small>
                  <?php } ?>
                </div>

                <div class="form-group">
                  <input type="submit" class="btn btn-primary btn-lg btn-block pb_btn-pill  btn-shadow-blue" value="Enviar">
                </div>
            <?php } ?>

            </form>

          </div>
        </div>
      </div>
    </section>
@endsection