@extends('site.site_layout')

@section('title')
Criar conta | Papirar Concursos
@endsection

@section('meta_description')
Crie sua conta no Papirar Concursos e teste por 7 dias a plataforma de questões para concursos internos militares.
@endsection

@section('robots')
noindex, follow
@endsection

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Teste grátis</span>
        <h1>Crie sua conta</h1>
        <p>Acesse o Papirar por 7 dias e comece a estudar por concurso, disciplina e tópico.</p>

        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro') }}" class="auth-form">
            @csrf
            <label>Nome
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Senha
                <input type="password" name="password" required>
            </label>
            <label>Confirmar senha
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="btn btn-primary full" type="submit">Criar conta gratuita</button>
        </form>

        <p class="auth-link">Já tem conta? <a href="{{ url('/login') }}">Entrar</a></p>
    </div>
</section>
@endsection
