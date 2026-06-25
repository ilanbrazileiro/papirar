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
        <span class="eyebrow">Recuperação</span>
        <h1>Esqueceu sua senha?</h1>
        <p>Informe seu e-mail para receber as instruções de recuperação.</p>

        @if(session('success'))
            <div class="auth-success-message">{{ session('success') }}</div>
        @endif

        @error('email')
            <div class="auth-error-message">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('auth.forgot-password.store') }}" class="auth-form" novalidate>
            @csrf
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="@error('email') auth-input-error @enderror">
            </label>

            <div class="auth-help">
                Se o e-mail estiver cadastrado, enviaremos um link para você criar uma nova senha.
            </div>

            <button class="btn btn-primary full" type="submit">Enviar link de recuperação</button>
        </form>

        <p class="auth-link"><a href="{{ url('/login') }}">Voltar para login</a></p>
    </div>
</section>
@endsection
