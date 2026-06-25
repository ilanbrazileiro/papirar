@extends('site.site_layout')

@section('title')
Entrar | Papirar Concursos
@endsection

@section('meta_description')
Acesse sua conta no Papirar Concursos para estudar questões de concursos militares.
@endsection

@section('robots')
noindex, follow
@endsection

@php
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro');
@endphp

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
        <span class="eyebrow">Área do aluno</span>
        <h1>Entrar no Papirar</h1>
        <p>Continue seus estudos por concurso, disciplina e tópico.</p>

        @if(session('success'))
            <div class="auth-success-message">{{ session('success') }}</div>
        @endif

        @if($errors->has('email') || $errors->has('password'))
            <div class="auth-error-message">
                {{ $errors->first('email') ?: $errors->first('password') }}
            </div>
        @endif

        <form method="POST" action="{{ route('auth.login.store') }}" class="auth-form" novalidate>
            @csrf
            <label>E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="@error('email') auth-input-error @enderror">
            </label>

            <label>Senha
                <input type="password" name="password" required class="@error('password') auth-input-error @enderror">
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
