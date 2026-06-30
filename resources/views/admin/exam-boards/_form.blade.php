<form method="POST" action="{{ $formAction }}">
    @csrf
    @if(($formMethod ?? 'POST') !== 'POST')
        @method($formMethod)
    @endif

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Nome da banca <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $examBoard->name ?? '') }}" maxlength="100" required placeholder="Ex.: FGV">
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $examBoard->slug ?? '') }}" maxlength="120" placeholder="Gerado automaticamente se ficar vazio">
                <div class="form-text">Ex.: <code>fgv</code>, <code>ibfc</code>, <code>cebraspe</code>.</div>
                @error('slug')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Observações internas sobre a banca.">{{ old('description', $examBoard->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" id="active" value="1" class="form-check-input" @checked(old('active', $examBoard->active ?? true))>
                <label for="active" class="form-check-label">Banca ativa</label>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.exam-boards.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button class="btn btn-primary">{{ $submitLabel ?? 'Salvar' }}</button>
        </div>
    </div>
</form>
