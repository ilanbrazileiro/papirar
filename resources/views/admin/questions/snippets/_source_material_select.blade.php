<div class="mb-3">
    <label for="source_material_id" class="form-label">Fonte / Bibliografia</label>
    <select
        name="source_material_id"
        id="source_material_id"
        class="form-control @error('source_material_id') is-invalid @enderror"
        data-placeholder="Selecione a fonte/material da questão">
        <option value="">Sem fonte específica</option>
        @if(!empty($selectedSourceMaterial))
            <option value="{{ $selectedSourceMaterial->id }}" selected>
                {{ $selectedSourceMaterial->title }}
                @if($selectedSourceMaterial->year)
                    - {{ $selectedSourceMaterial->year }}
                @endif
            </option>
        @elseif(old('source_material_id'))
            @php($oldMaterial = \App\Models\SourceMaterial::find(old('source_material_id')))
            @if($oldMaterial)
                <option value="{{ $oldMaterial->id }}" selected>{{ $oldMaterial->title }}</option>
            @endif
        @endif
    </select>
    @error('source_material_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">
        Use este campo para indicar o manual, lei, norma ou edital que fundamenta a questão.
    </small>
</div>
