@extends('site.site_layout')
@section('content')
    <div class="container py-5" style="max-width:480px;">
        @include('components.flash')
        <h2 class="mb-4">Entrar</h2>
        <form method="POST" action="{{ route('auth.login.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">E-mail</label><input class="form-control" type="email" name="email"
                    value="{{ old('email') }}" required>
            </div>
            {{-- show error--}}
                @error('email')
                    <div class="text-danger">{{ $message }}</div>                                    
                @enderror
            <div class="mb-3"><label class="form-label">Senha</label><input class="form-control" type="password"
                    name="password" value="{{ old('password')}}" required>
            </div>
            {{-- show error--}}
                @error('password')
                    <div class="text-danger">{{ $message }}</div>                                    
                @enderror
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" value="1"
                    id="remember"><label class="form-check-label" for="remember">Lembrar de mim</label>
            </div>
            
            <button class="btn btn-primary w-100">Entrar</button>
        </form>
        <div class="mt-3"><a href="{{ route('auth.forgot-password') }}">Esqueci a senha</a></div>
    </div>
@endsection
