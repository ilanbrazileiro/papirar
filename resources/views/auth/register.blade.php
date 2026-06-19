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

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Conta gratuita</span>
        <h1>Crie sua conta</h1>
        <p>
            Cadastre-se para acessar sua área do aluno, conhecer os cursos disponíveis e escolher a preparação ideal para o seu concurso.
        </p>

        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro') }}" class="auth-form">
            @csrf

            <input type="text" name="seguranca" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;">

            <label>Nome
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            @error('name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror

            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror

            <label>Senha
                <input type="password" name="password" required>
            </label>
            @error('password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror

            <label>Confirmar senha
                <input type="password" name="password_confirmation" required>
            </label>

            <button class="btn btn-primary full" type="submit">
                Criar conta gratuita
            </button>
        </form>

        <p class="auth-link">Já tem conta? <a href="{{ url('/login') }}">Entrar</a></p>
    </div>
</section>
@endsection
