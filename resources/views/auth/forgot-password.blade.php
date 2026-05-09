@extends('site.site_layout')

@section('title')
Recuperar senha | Papirar Concursos
@endsection

@section('meta_description')
Recupere o acesso à sua conta do Papirar Concursos.
@endsection

@section('robots')
noindex, follow
@endsection

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Recuperação</span>
        <h1>Esqueceu sua senha?</h1>
        <p>Informe seu e-mail para receber as instruções de recuperação.</p>

        <form method="POST" action="{{ url('/esqueci-a-senha') }}" class="auth-form">
            @csrf
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <button class="btn btn-primary full" type="submit">Enviar link de recuperação</button>
        </form>

        <p class="auth-link"><a href="{{ url('/login') }}">Voltar para login</a></p>
    </div>
</section>
@endsection
