@csrf

<div class="row">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="title" class="form-label">Título da fonte/material *</label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $material->title) }}" required>
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $material->slug) }}" placeholder="gerado automaticamente se vazio">
            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="corporation_id" class="form-label">Corporação</label>
            <select name="corporation_id" id="corporation_id" class="form-control @error('corporation_id') is-invalid @enderror">
                <option value="">Geral / não vinculada</option>
                @foreach($corporations as $corporation)
                    <option value="{{ $corporation->id }}" @selected((string) old('corporation_id', $material->corporation_id) === (string) $corporation->id)>
                        {{ $corporation->name }}
                    </option>
                @endforeach
            </select>
            @error('corporation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="subject_id" class="form-label">Disciplina *</label>
            <select name="subject_id" id="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                <option value="">Selecione</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) old('subject_id', $material->subject_id) === (string) $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="material_type" class="form-label">Tipo *</label>
            <select name="material_type" id="material_type" class="form-control @error('material_type') is-invalid @enderror" required>
                @foreach(['manual' => 'Manual', 'lei' => 'Lei', 'norma' => 'Norma/POP', 'edital' => 'Edital', 'livro' => 'Livro', 'outro' => 'Outro'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('material_type', $material->material_type ?? 'manual') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('material_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="year" class="form-label">Ano</label>
            <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', $material->year) }}">
            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="mb-3">
            <label for="reference_code" class="form-label">Código/referência</label>
            <input type="text" name="reference_code" id="reference_code" class="form-control @error('reference_code') is-invalid @enderror" value="{{ old('reference_code', $material->reference_code) }}">
            @error('reference_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-md-5">
        <div class="mb-3">
            <label for="url" class="form-label">URL pública</label>
            <input type="text" name="url" id="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url', $material->url) }}">
            @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Descrição</label>
    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $material->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check mb-3">
    <input type="hidden" name="active" value="0">
    <input type="checkbox" name="active" id="active" value="1" class="form-check-input" @checked(old('active', $material->active ?? true))>
    <label for="active" class="form-check-label">Ativo</label>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="{{ route('admin.source-materials.index') }}" class="btn btn-secondary">Voltar</a>
</div>
