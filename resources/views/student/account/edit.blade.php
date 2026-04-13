@extends('layouts.student')

@section('title', 'Minha conta')

@section('content')
    <div class="mb-4">
        <h1 class="page-title">Minha conta</h1>
        <p class="page-subtitle">Atualize seus dados com segurança. Alterações de senha ficam separadas do cadastro básico.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-soft p-4 p-md-5">
                <h2 class="section-title">Dados pessoais</h2>
                <hr>

                <form method="POST" action="{{ route('student.account.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <input type="hidden" name="email" value="{{ old('email', $user->email) }}" required>

                        <div class="class col-md-12">

                            <label class="form-label">E-mail: {{ old('email', $user->email) }}</label>
                            
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data de nascimento</label>
                            <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="{{ old('cpf', $user->cpf) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">DDD</label>
                            <input type="text" name="ddd" class="form-control" value="{{ old('ddd', $user->ddd) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="section-title">Endereço</h3>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <input type="text" name="address[cep]" class="form-control" value="{{ old('address.cep', $user->address->cep ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logradouro</label>
                            <input type="text" name="address[street]" class="form-control" value="{{ old('address.street', $user->address->street ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Número</label>
                            <input type="text" name="address[number]" class="form-control" value="{{ old('address.number', $user->address->number ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="address[complement]" class="form-control" value="{{ old('address.complement', $user->address->complement ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="address[neighborhood]" class="form-control" value="{{ old('address.neighborhood', $user->address->neighborhood ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" name="address[state]" maxlength="2" class="form-control" value="{{ old('address.state', $user->address->state ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="address[city]" class="form-control" value="{{ old('address.city', $user->address->city ?? '') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-soft p-4">
                <h2 class="section-title">Alterar senha</h2>

                <form method="POST" action="{{ route('student.account.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Senha atual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirme a nova senha</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button class="btn btn-outline-primary w-100">Atualizar senha</button>
                </form>
            </div>
        </div>
    </div>
@endsection
