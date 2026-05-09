@extends('site.site_layout')

@section('title')
Entrar | Papirar Concursos
@endsection

@section('meta_description')
Acesse sua conta no Papirar Concursos para estudar questões de concursos internos militares.
@endsection

@section('robots')
noindex, follow
@endsection

@section('content')
@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro');
@endphp

<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Área do aluno</span>
        <h1>Entrar no Papirar</h1>
        <p>Continue seus estudos por concurso, disciplina e tópico.</p>

        <form method="POST" action="{{ url('/login') }}" class="auth-form">
            @csrf
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>Senha
                <input type="password" name="password" required>
            </label>
            <button class="btn btn-primary full" type="submit">Entrar</button>
        </form>

        <div class="auth-extra">
            <a href="{{ url('/esqueci-a-senha') }}">Esqueci minha senha</a>
            <a href="{{ $registerUrl }}">Criar conta grátis</a>
        </div>
    </div>
</section>
@endsection
