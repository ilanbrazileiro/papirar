@csrf

<div class="row g-3">
    <div class="col-md-7">
        <label class="form-label">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
    </div>

    <div class="col-md-5">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $subject->slug) }}" placeholder="Preenchimento automático se vazio">
    </div>

    <div class="col-12">
        <label class="form-label">Descrição</label>
        <textarea name="description" rows="5" class="form-control">{{ old('description', $subject->description) }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked(old('active', $subject->active))>
            <label class="form-check-label" for="active">Disciplina ativa</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary">{{ $submitLabel }}</button>
</div>
