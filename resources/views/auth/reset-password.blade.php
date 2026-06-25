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

@push('head')
<style>
    .auth-form label { display: block; }
    .auth-input-error { border-color: #dc2626 !important; box-shadow: 0 0 0 3px rgba(220, 38, 38, .12) !important; }
    .auth-error-message { margin-top: 6px; margin-bottom: 12px; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 9px 11px; font-size: 13px; font-weight: 700; line-height: 1.4; }
    .auth-success-message { margin: 0 0 16px; color: #0f5132; background: #ecfdf3; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; font-size: 13px; font-weight: 700; line-height: 1.4; }
    .auth-help { color: #6b7280; font-size: 13px; line-height: 1.45; margin-top: -4px; margin-bottom: 12px; }
</style>
@endpush

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Nova senha</span>
        <h1>Redefinir senha</h1>
        <p>Informe seu e-mail e crie uma nova senha de acesso.</p>

        @if($errors->any())
            <div class="auth-error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="auth-form" novalidate>
            @csrf

            <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

            <label>E-mail
                <input type="email" name="email" value="{{ old('email', $email ?? request('email')) }}" required autofocus class="@error('email') auth-input-error @enderror">
            </label>

            <label>Nova senha
                <input type="password" name="password" required class="@error('password') auth-input-error @enderror">
            </label>

            <label>Confirmar senha
                <input type="password" name="password_confirmation" required class="@error('password') auth-input-error @enderror">
            </label>

            <button class="btn btn-primary full" type="submit">Salvar nova senha</button>
        </form>

        <p class="auth-link"><a href="{{ url('/login') }}">Voltar para login</a></p>
    </div>
</section>
@endsection
