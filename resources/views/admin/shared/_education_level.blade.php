@php
    // Reutilizável nos formulários de questões, concursos e cursos.
    // O formulário pai deve fornecer $record (model ou null).
    $selectedEducationLevel = old('education_level', $record->education_level ?? '');
@endphp
<div class="col-md-4">
    <label for="education_level" class="form-label">Nível de escolaridade</label>
    <select name="education_level" id="education_level" class="form-control" required>
        <option value="">Selecione o nível</option>
        @foreach(\App\Support\EducationLevel::options() as $value => $label)
            <option value="{{ $value }}" @selected($selectedEducationLevel === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('education_level')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
    <div class="text-muted small mt-1">Classificação própria deste cadastro; não é herdada do concurso vinculado.</div>
</div>
