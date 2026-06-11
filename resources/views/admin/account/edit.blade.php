@extends('layouts.admin')

@section('title', 'Minha conta | Admin Papirar')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3 mb-1">Minha conta</h1>
            <p class="text-muted mb-0">Atualize seus dados de acesso e altere sua senha administrativa.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Dados da conta</h3>
                </div>

                <form method="POST" action="{{ route('admin.account.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($hasPhoneColumn)
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="mb-0">
                            <label class="form-label">Perfil</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->role ?? 'admin') }}" disabled>
                            <small class="text-muted">O perfil de acesso só pode ser alterado no cadastro de colaboradores.</small>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Alterar senha</h3>
                </div>

                <form method="POST" action="{{ route('admin.account.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Senha atual</label>
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nova senha</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-0">
                            <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-warning">Atualizar senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
