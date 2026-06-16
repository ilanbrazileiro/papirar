@csrf

<div class="card mb-4">
    <div class="card-header">
        <strong>Dados do acesso</strong>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="user_id">Aluno</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((int) old('user_id', $access->user_id) === (int) $user->id)>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="course_id">Curso</label>
                    <select name="course_id" id="course_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) old('course_id', $access->course_id) === (int) $course->id)>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $access->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="starts_at">Início</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" class="form-control" value="{{ old('starts_at', optional($access->starts_at)->format('Y-m-d\TH:i')) }}" required>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="ends_at">Fim do acesso</label>
                    <input type="datetime-local" name="ends_at" id="ends_at" class="form-control" value="{{ old('ends_at', optional($access->ends_at)->format('Y-m-d\TH:i')) }}">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="bonus_days">Bônus de dias</label>
                    <input type="number" name="bonus_days" id="bonus_days" min="0" max="365" class="form-control" value="{{ old('bonus_days', $access->bonus_days ?? 0) }}">
                </div>
            </div>
        </div>

        <div class="form-check mt-2">
            <input type="hidden" name="cancel_at_period_end" value="0">
            <input type="checkbox" name="cancel_at_period_end" id="cancel_at_period_end" value="1" class="form-check-input" @checked(old('cancel_at_period_end', $access->cancel_at_period_end))>
            <label for="cancel_at_period_end" class="form-check-label">Cancelar ao fim do período vigente</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('admin.course-accesses.index') }}" class="btn btn-secondary">Voltar</a>
    <button type="submit" class="btn btn-primary">Salvar</button>
</div>
