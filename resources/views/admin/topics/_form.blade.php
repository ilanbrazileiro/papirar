@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Disciplina</label>
        <select name="subject_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $topic->subject_id) == $subject->id)>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nome do assunto</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $topic->name) }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $topic->slug) }}" placeholder="Preenchimento automático se vazio">
    </div>

    <div class="col-12">
        <label class="form-label">Descrição</label>
        <textarea name="description" rows="5" class="form-control">{{ old('description', $topic->description) }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked(old('active', $topic->active))>
            <label class="form-check-label" for="active">Assunto ativo</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.topics.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button class="btn btn-primary">{{ $submitLabel }}</button>
</div>
