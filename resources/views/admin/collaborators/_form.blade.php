@csrf

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Revise os campos abaixo.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Dados do colaborador</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Nome</label>
                <input type="text" name="name" id="name" value="{{ old('name', $collaborator->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email', $collaborator->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="role" class="form-label">Perfil de acesso</label>
                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $collaborator->role) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">
                    Para colaborador de questões e conteúdo, use o perfil <strong>Conteúdo</strong>.
                </small>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" @checked((bool) old('is_active', $collaborator->is_active ?? true))>
                    <label for="is_active" class="form-check-label">Colaborador ativo</label>
                </div>
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">Senha</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                <small class="text-muted">
                    @if($collaborator->exists)
                        Preencha apenas se quiser alterar a senha.
                    @else
                        Se deixar em branco, a senha inicial será 12345678.
                    @endif
                </small>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">Confirmar senha</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Perfis sugeridos</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <strong>Conteúdo</strong>
                    <p class="text-muted mb-0">Perfil indicado para parceiro que cadastra e revisa questões, corporações, disciplinas, tópicos, bibliografias e concursos.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <strong>Administrador</strong>
                    <p class="text-muted mb-0">Use apenas para quem pode gerenciar todo o sistema, incluindo colaboradores, clientes, planos e assinaturas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between gap-2">
    <a href="{{ route('admin.collaborators.index') }}" class="btn btn-outline-secondary">Voltar</a>

    <div class="d-flex gap-2">
        @if($collaborator->exists && $collaborator->id !== auth()->id())
            <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('delete-collaborator-form').submit();">
                Remover
            </button>
        @endif
        <button type="submit" class="btn btn-primary">Salvar colaborador</button>
    </div>
</div>
