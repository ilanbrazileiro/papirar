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

                <form method="POST" action="{{ route('student.account.update') }}" id="account-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <input type="hidden" name="email" value="{{ old('email', $user->email) }}" required>

                        <div class="col-md-12">
                            <label class="form-label">E-mail</label>
                            <div class="form-control bg-light text-muted">{{ old('email', $user->email) }}</div>
                            <div class="small-muted mt-1">O e-mail é usado para login e comunicações da conta.</div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de nascimento</label>
                            <input type="date" name="birth_date" id="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                            @error('birth_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback" id="birth-date-feedback">A idade permitida deve estar entre 10 e 90 anos.</div>
                            @enderror
                            <div class="small-muted mt-1">Permitido para alunos entre 10 e 90 anos.</div>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">CPF</label>
                            <input type="text" name="cpf" id="cpf" class="form-control @error('cpf') is-invalid @enderror" value="{{ old('cpf', $user->cpf) }}" maxlength="14" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00">
                            @error('cpf')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback" id="cpf-feedback">Informe um CPF válido.</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">DDD</label>
                            <input type="text" name="ddd" id="ddd" class="form-control @error('ddd') is-invalid @enderror" value="{{ old('ddd', $user->ddd) }}" maxlength="2" inputmode="numeric" autocomplete="off" placeholder="21">
                            @error('ddd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" maxlength="10" inputmode="numeric" autocomplete="off" placeholder="99999-9999">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="section-title">Endereço</h3>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <input type="text" name="address[cep]" id="cep" class="form-control @error('address.cep') is-invalid @enderror" value="{{ old('address.cep', $user->address->cep ?? '') }}" maxlength="9" inputmode="numeric" autocomplete="off" placeholder="00000-000">
                            @error('address.cep') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="small-muted mt-1" id="cep-status"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Logradouro</label>
                            <input type="text" name="address[street]" id="street" class="form-control @error('address.street') is-invalid @enderror" value="{{ old('address.street', $user->address->street ?? '') }}">
                            @error('address.street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Número</label>
                            <input type="text" name="address[number]" id="number" class="form-control @error('address.number') is-invalid @enderror" value="{{ old('address.number', $user->address->number ?? '') }}">
                            @error('address.number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="address[complement]" id="complement" class="form-control @error('address.complement') is-invalid @enderror" value="{{ old('address.complement', $user->address->complement ?? '') }}">
                            @error('address.complement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="address[neighborhood]" id="neighborhood" class="form-control @error('address.neighborhood') is-invalid @enderror" value="{{ old('address.neighborhood', $user->address->neighborhood ?? '') }}">
                            @error('address.neighborhood') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" name="address[state]" id="state" maxlength="2" class="form-control text-uppercase @error('address.state') is-invalid @enderror" value="{{ old('address.state', $user->address->state ?? '') }}">
                            @error('address.state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="address[city]" id="city" class="form-control @error('address.city') is-invalid @enderror" value="{{ old('address.city', $user->address->city ?? '') }}">
                            @error('address.city') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirme a nova senha</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button class="btn btn-outline-primary w-100">Atualizar senha</button>
                </form>
            </div>

            <div class="card-soft p-4 mt-4">
                <h2 class="section-title">Privacidade</h2>
                <p class="small-muted mb-0">Seus dados ajudam a validar seu cadastro e melhorar a segurança da sua conta.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const onlyDigits = value => (value || '').replace(/\D+/g, '');
    const accountForm = document.getElementById('account-form');
    const cpfInput = document.getElementById('cpf');
    const dddInput = document.getElementById('ddd');
    const phoneInput = document.getElementById('phone');
    const birthDateInput = document.getElementById('birth_date');
    const cepInput = document.getElementById('cep');
    const cepStatus = document.getElementById('cep-status');

    function applyCpfMask(value) {
        value = onlyDigits(value).slice(0, 11);
        if (value.length > 9) return value.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
        if (value.length > 6) return value.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
        if (value.length > 3) return value.replace(/(\d{3})(\d{0,3})/, '$1.$2');
        return value;
    }

    function applyPhoneMask(value) {
        value = onlyDigits(value).slice(0, 9);
        if (value.length > 5) return value.replace(/(\d{5})(\d{0,4})/, '$1-$2');
        if (value.length > 4) return value.replace(/(\d{4})(\d{0,4})/, '$1-$2');
        return value;
    }

    function applyCepMask(value) {
        value = onlyDigits(value).slice(0, 8);
        if (value.length > 5) return value.replace(/(\d{5})(\d{0,3})/, '$1-$2');
        return value;
    }

    function isValidCpf(value) {
        const cpf = onlyDigits(value);
        if (cpf.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        for (let t = 9; t < 11; t++) {
            let sum = 0;
            for (let i = 0; i < t; i++) sum += parseInt(cpf[i], 10) * ((t + 1) - i);
            const digit = ((10 * sum) % 11) % 10;
            if (parseInt(cpf[t], 10) !== digit) return false;
        }

        return true;
    }

    function isValidBirthDate(value) {
        if (!value) return true;
        const birthDate = new Date(value + 'T00:00:00');
        if (Number.isNaN(birthDate.getTime())) return false;

        const today = new Date();
        const minDate = new Date(today);
        minDate.setFullYear(today.getFullYear() - 90);
        minDate.setHours(0, 0, 0, 0);

        const maxDate = new Date(today);
        maxDate.setFullYear(today.getFullYear() - 10);
        maxDate.setHours(23, 59, 59, 999);

        return birthDate >= minDate && birthDate <= maxDate;
    }

    function validateCpfField() {
        if (!cpfInput) return true;
        const cpf = onlyDigits(cpfInput.value);

        if (!cpf) {
            cpfInput.setCustomValidity('');
            cpfInput.classList.remove('is-invalid');
            return true;
        }

        if (!isValidCpf(cpf)) {
            cpfInput.setCustomValidity('Informe um CPF válido.');
            cpfInput.classList.add('is-invalid');
            return false;
        }

        cpfInput.setCustomValidity('');
        cpfInput.classList.remove('is-invalid');
        return true;
    }

    function validateBirthDateField() {
        if (!birthDateInput) return true;

        if (!isValidBirthDate(birthDateInput.value)) {
            birthDateInput.setCustomValidity('A idade permitida deve estar entre 10 e 90 anos.');
            birthDateInput.classList.add('is-invalid');
            return false;
        }

        birthDateInput.setCustomValidity('');
        birthDateInput.classList.remove('is-invalid');
        return true;
    }

    if (cpfInput) {
        cpfInput.value = applyCpfMask(cpfInput.value);
        cpfInput.addEventListener('input', function () {
            this.value = applyCpfMask(this.value);
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        });
        cpfInput.addEventListener('blur', validateCpfField);
    }

    if (birthDateInput) {
        birthDateInput.addEventListener('change', validateBirthDateField);
        birthDateInput.addEventListener('blur', validateBirthDateField);
    }

    if (dddInput) {
        dddInput.value = onlyDigits(dddInput.value).slice(0, 2);
        dddInput.addEventListener('input', function () { this.value = onlyDigits(this.value).slice(0, 2); });
    }

    if (phoneInput) {
        phoneInput.value = applyPhoneMask(phoneInput.value);
        phoneInput.addEventListener('input', function () { this.value = applyPhoneMask(this.value); });
    }

    if (cepInput) {
        cepInput.value = applyCepMask(cepInput.value);
        cepInput.addEventListener('input', function () { this.value = applyCepMask(this.value); });
        cepInput.addEventListener('blur', function () {
            const cep = onlyDigits(this.value);
            if (cep.length !== 8) {
                if (cepStatus) cepStatus.textContent = '';
                return;
            }

            if (cepStatus) {
                cepStatus.textContent = 'Buscando endereço...';
                cepStatus.classList.remove('text-danger');
            }

            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(response => {
                    if (!response.ok) throw new Error('Falha na busca do CEP.');
                    return response.json();
                })
                .then(data => {
                    if (data.erro) {
                        if (cepStatus) {
                            cepStatus.textContent = 'CEP não encontrado.';
                            cepStatus.classList.add('text-danger');
                        }
                        return;
                    }

                    const street = document.getElementById('street');
                    const neighborhood = document.getElementById('neighborhood');
                    const city = document.getElementById('city');
                    const state = document.getElementById('state');
                    const number = document.getElementById('number');

                    if (street) street.value = data.logradouro || '';
                    if (neighborhood) neighborhood.value = data.bairro || '';
                    if (city) city.value = data.localidade || '';
                    if (state) state.value = data.uf || '';

                    if (cepStatus) {
                        cepStatus.textContent = 'Endereço encontrado.';
                        cepStatus.classList.remove('text-danger');
                    }

                    if (number && !number.value) number.focus();
                })
                .catch(() => {
                    if (cepStatus) {
                        cepStatus.textContent = 'Não foi possível buscar o CEP agora.';
                        cepStatus.classList.add('text-danger');
                    }
                });
        });
    }

    const stateInput = document.getElementById('state');
    if (stateInput) {
        stateInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 2);
        });
    }

    if (accountForm) {
        accountForm.addEventListener('submit', function (event) {
            const cpfOk = validateCpfField();
            const birthDateOk = validateBirthDateField();
            if (!cpfOk || !birthDateOk) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
});
</script>
@endpush
