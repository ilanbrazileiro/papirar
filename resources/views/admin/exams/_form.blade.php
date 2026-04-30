@csrf

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Corporação</label>
            <select name="corporation_id" class="form-control" required>
                <option value="">Selecione...</option>
                @foreach($corporations as $corporation)
                    <option value="{{ $corporation->id }}" @selected(old('corporation_id', $exam->corporation_id ?? null) == $corporation->id)>
                        {{ $corporation->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Nome do concurso</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $exam->name ?? '') }}" placeholder="Ex.: CHOE 2026" required>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Ano</label>
            <input type="number" name="year" class="form-control" value="{{ old('year', $exam->year ?? '') }}" placeholder="2026">
        </div>
    </div>
</div>

<div class="form-group">
    <label>Descrição / observações</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Ex.: Concurso previsto conforme edital publicado em 2026.">{{ old('description', $exam->description ?? '') }}</textarea>
</div>

<div class="card border">
    <div class="card-header bg-light">
        <strong>Disciplinas cobradas no concurso</strong>
        <small class="text-muted d-block">Essas disciplinas aparecerão como checkbox para o aluno.</small>
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
