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

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Corporação</label>
            <select name="corporation_id" class="form-control" required>
                <option value="">Selecione...</option>
                @foreach($corporations as $corporation)
                    <option value="{{ $corporation->id }}" @selected(old('corporation_id', $exam->corporation_id) == $corporation->id)>
                        {{ $corporation->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Nome do concurso</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $exam->title) }}" placeholder="Ex.: CHOE PMERJ 2026" required>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>Ano</label>
            <input type="number" name="year" class="form-control" value="{{ old('year', $exam->year) }}" placeholder="2026" required>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="planned" @selected(old('status', $exam->status ?? 'planned') === 'planned')>Previsto</option>
                <option value="published" @selected(old('status', $exam->status ?? 'planned') === 'published')>Publicado</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Tipo do concurso</label>
            <input type="text" name="exam_type" class="form-control" value="{{ old('exam_type', $exam->exam_type ?? 'concurso_interno') }}" placeholder="Ex.: CHOE, CHOAE, CFS" required>
            <small class="text-muted">Use um tipo simples e consistente, como CHOE, CHOAE ou CFO.</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group pt-md-4 mt-md-2">
            <div class="custom-control custom-switch">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" class="custom-control-input" id="active" @checked(old('active', $exam->active ?? true))>
                <label class="custom-control-label" for="active">Ativo para aparecer ao aluno</label>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Descrição / observações</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Ex.: Concurso previsto com base no edital de referência.">{{ old('description', $exam->description) }}</textarea>
</div>

<div class="card border">
    <div class="card-header bg-light">
        <strong>Disciplinas cobradas</strong>
        <small class="text-muted d-block">O aluno escolherá entre estas disciplinas antes de iniciar a sessão.</small>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($subjects as $subject)
                <div class="col-md-4 mb-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                               name="subject_ids[]"
                               value="{{ $subject->id }}"
                               class="custom-control-input"
                               id="subject_{{ $subject->id }}"
                               @checked(in_array($subject->id, old('subject_ids', $selectedSubjects ?? [])))>
                        <label class="custom-control-label" for="subject_{{ $subject->id }}">
                            {{ $subject->name }}
                            @if(($subject->scope ?? 'general') === 'corporation_specific')
                                <span class="badge badge-warning">específica</span>
                            @else
                                <span class="badge badge-secondary">geral</span>
                            @endif
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary">Salvar concurso</button>
    <a href="{{ route('admin.planned-exams.index') }}" class="btn btn-light">Cancelar</a>
</div>
