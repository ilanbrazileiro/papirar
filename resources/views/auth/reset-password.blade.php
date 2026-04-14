@extends('site.site_layout')

@section('content')
<div class="container py-5" style="max-width:480px;">
    @include('components.flash')
    <h2 class="mb-4">Redefinir senha</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $email) }}" required>
            @error('email')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Nova senha</label>
            <input class="form-control" type="password" name="password" required>
            @error('password')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmar nova senha</label>
            <input class="form-control" type="password" name="password_confirmation" required>
        </div>

        <button class="btn btn-primary w-100">Salvar nova senha</button>
    </form>
</div>
@endsection