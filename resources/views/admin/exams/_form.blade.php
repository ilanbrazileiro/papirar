@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Corporação</label>
        <select name="corporation_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($corporations as $corporation)
                <option value="{{ $corporation->id }}" @selected(old('corporation_id', $exam->corporation_id) == $corporation->id)>
                    {{ $corporation->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Ano</label>
        <input type="number" name="year" class="form-control" value="{{ old('year', $exam->year) }}" min="1900" max="2100" required>
    </div>

    <div class="col-md-8">
        <label class="form-label">Título do concurso</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $exam->title) }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <input type="text" name="exam_type" class="form-control" value="{{ old('exam_type', $exam->exam_type) }}" placeholder="Ex.: Prova objetiva" required>
    </div>

    <div class="col-12">
        <label class="form-label">Descrição</label>
        <textarea name="description" rows="5" class="form-control">{{ old('description', $exam->description) }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked(old('active', $exam->active))>
            <label class="form-check-label" for="active">Concurso ativo</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary">{{ $submitLabel }}</button>
</div>
