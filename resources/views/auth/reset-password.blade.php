@extends('site.site_layout')

@section('title')
Redefinir senha | Papirar Concursos
@endsection

@section('meta_description')
Redefina sua senha de acesso ao Papirar Concursos.
@endsection

@section('robots')
noindex, follow
@endsection

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Nova senha</span>
        <h1>Redefinir senha</h1>
        <p>Informe seu e-mail e crie uma nova senha de acesso.</p>

        <form method="POST" action="{{ url('/esqueci-a-senha') }}" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">
            <label>E-mail
                <input type="email" name="email" value="{{ old('email', request('email')) }}" required autofocus>
            </label>
            <label>Nova senha
                <input type="password" name="password" required>
            </label>
            <label>Confirmar senha
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="btn btn-primary full" type="submit">Salvar nova senha</button>
        </form>
    </div>
</section>
@endsection
