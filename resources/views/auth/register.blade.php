@extends('site.site_layout')

@section('title')
Criar conta | Papirar Concursos
@endsection

@section('meta_description')
Crie sua conta gratuita no Papirar Concursos e conheça os cursos disponíveis para sua preparação.
@endsection

@section('robots')
noindex, follow
@endsection

@push('head')
<style>
    .auth-form label {
        display: block;
    }

    .auth-input-error {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .12) !important;
    }

    .auth-error-message {
        margin-top: 6px;
        margin-bottom: 12px;
        color: #b91c1c;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 9px 11px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }
</style>
@endpush

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Conta gratuita</span>
        <h1>Crie sua conta</h1>
        <p>
            Cadastre-se para acessar sua área do aluno, conhecer os cursos disponíveis e escolher a preparação ideal para o seu concurso.
        </p>

        <form method="POST" action="{{ url('/cadastro') }}" class="auth-form" novalidate>
            @csrf

            <input type="text" name="seguranca" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;">

            <label>Nome
                <input type="text" name="name" value="{{ old('name') }}" required autofocus class="@error('name') auth-input-error @enderror">
            </label>
            @error('name')
                <div class="auth-error-message">{{ $message }}</div>
            @enderror

            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required class="@error('email') auth-input-error @enderror">
            </label>
            @error('email')
                <div class="auth-error-message">
                    {{ $message }}
                    @if(str_contains($message, 'já está cadastrado'))
                        <div style="font-weight:600;margin-top:4px;">
                            Tente entrar na sua conta ou recupere a senha se não lembrar.
                        </div>
                    @endif
                </div>
            @enderror

            <label>Senha
                <input type="password" name="password" required class="@error('password') auth-input-error @enderror">
            </label>
            @error('password')
                <div class="auth-error-message">{{ $message }}</div>
            @enderror

            <label>Confirmar senha
                <input type="password" name="password_confirmation" required class="@error('password') auth-input-error @enderror">
            </label>

            <button class="btn btn-primary full" type="submit">
                Criar conta gratuita
            </button>
        </form>

        <p class="auth-link">Já tem conta? <a href="{{ url('/login') }}">Entrar</a></p>
    </div>
</section>
@endsection
