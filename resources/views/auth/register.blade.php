@extends('site.site_layout')

@section('title')
Criar conta | Papirar Concursos
@endsection

@section('meta_description')
Crie sua conta no Papirar Concursos, escolha um curso e teste gratuitamente por 7 dias.
@endsection

@section('robots')
noindex, follow
@endsection

@section('content')
<section class="auth-page">
    <div class="auth-card">
        <span class="eyebrow">Teste grátis</span>
        <h1>Crie sua conta</h1>
        <p>Escolha um curso e receba acesso gratuito por 7 dias para testar o Papirar.</p>

        @if(($trialCourses ?? collect())->isEmpty())
            <div class="alert alert-warning">
                Nenhum curso está disponível para teste gratuito no momento. Tente novamente mais tarde.
            </div>
        @endif

        <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/cadastro') }}" class="auth-form">
            @csrf

            <label>Curso para teste gratuito
                <select name="course_id" required {{ ($trialCourses ?? collect())->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Selecione um curso</option>
                    @foreach($trialCourses ?? [] as $course)
                        <option value="{{ $course->id }}" @selected((int) old('course_id') === (int) $course->id)>
                            {{ $course->title }} — {{ $course->trialDaysForAccess() }} dias grátis
                        </option>
                    @endforeach
                </select>
            </label>
            @error('course_id')
                <div class="text-danger small">{{ $message }}</div>
            @enderror

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

            <button class="btn btn-primary full" type="submit" {{ ($trialCourses ?? collect())->isEmpty() ? 'disabled' : '' }}>
                Criar conta gratuita
            </button>
        </form>

        <p class="auth-link">Já tem conta? <a href="{{ url('/login') }}">Entrar</a></p>
    </div>
</section>
@endsection
