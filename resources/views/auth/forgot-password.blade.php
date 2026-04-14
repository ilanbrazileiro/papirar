@extends('site.site_layout')

@section('content')
<div class="container py-5" style="max-width:480px;">
    @include('components.flash')
    <h2 class="mb-4">Recuperar senha</h2>

    <form method="POST" action="{{ route('auth.forgot-password.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary w-100">Enviar link</button>
    </form>

    <div class="mt-3 text-center">
        <a href="{{ route('auth.login') }}">Voltar para o login</a>
    </div>
</div>
@endsection
