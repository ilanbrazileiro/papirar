@csrf

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Verifique os campos abaixo.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header">Questão vinculada</div>
    <div class="card-body">
        <div class="form-group mb-3">
            <label for="question_id">Questão</label>
            <select name="question_id" id="question_id" class="form-control" required>
                <option value="">Selecione uma questão</option>
                @foreach($questions as $questionOption)
                    <option value="{{ $questionOption->id }}" @selected((int) old('question_id', $lesson->question_id) === (int) $questionOption->id)>
                        #{{ $questionOption->id }} — {{ \Illuminate\Support\Str::limit(strip_tags($questionOption->statement), 120) }}
                        @if($questionOption->subject) — {{ $questionOption->subject->name }} @endif
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Cada questão pode ter uma aula principal. Para buscar outra questão, use o campo abaixo e recarregue a tela.</small>
        </div>

        <div class="form-row">
            <div class="form-group col-md-8 mb-3">
                <label for="question_search">Buscar questão nesta tela</label>
                <input type="text" name="question_search" id="question_search" value="{{ request('question_search') }}" class="form-control" placeholder="ID da questão ou trecho do enunciado">
            </div>
            <div class="form-group col-md-4 mb-3 d-flex align-items-end">
                <button type="submit" formaction="{{ request()->url() }}" formmethod="GET" class="btn btn-outline-secondary w-100">Buscar questão</button>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Dados da aula</div>
    <div class="card-body">
        <div class="form-group mb-3">
            <label for="title">Título da aula</label>
            <input type="text" name="title" id="title" value="{{ old('title', $lesson->title) }}" class="form-control" required maxlength="255" placeholder="Ex.: Resolução comentada da questão">
        </div>

        <div class="form-row">
            <div class="form-group col-md-4 mb-3">
                <label for="provider">Plataforma</label>
                <select name="provider" id="provider" class="form-control" required>
                    <option value="youtube" @selected(old('provider', $lesson->provider) === 'youtube')>YouTube</option>
                    <option value="vimeo" @selected(old('provider', $lesson->provider) === 'vimeo')>Vimeo</option>
                    <option value="external" @selected(old('provider', $lesson->provider) === 'external')>Link externo</option>
                    <option value="html" @selected(old('provider', $lesson->provider) === 'html')>Embed HTML/manual</option>
                </select>
            </div>
            <div class="form-group col-md-4 mb-3">
                <label for="visibility">Visibilidade</label>
                <select name="visibility" id="visibility" class="form-control" required>
                    <option value="course_access" @selected(old('visibility', $lesson->visibility) === 'course_access')>Somente alunos com acesso ao curso</option>
                    <option value="public" @selected(old('visibility', $lesson->visibility) === 'public')>Pública</option>
                </select>
            </div>
            <div class="form-group col-md-4 mb-3">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active" @selected(old('status', $lesson->status) === 'active')>Ativa</option>
                    <option value="inactive" @selected(old('status', $lesson->status) === 'inactive')>Inativa</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="video_url">URL do vídeo</label>
            <input type="url" name="video_url" id="video_url" value="{{ old('video_url', $lesson->video_url) }}" class="form-control" maxlength="1000" placeholder="https://www.youtube.com/watch?v=...">
            <small class="form-text text-muted">Para YouTube/Vimeo, informe a URL normal. O sistema tentará gerar o embed automaticamente.</small>
        </div>

        <div class="form-group mb-3">
            <label for="embed_url">URL de embed</label>
            <input type="url" name="embed_url" id="embed_url" value="{{ old('embed_url', $lesson->embed_url) }}" class="form-control" maxlength="1000" placeholder="Opcional. Ex.: https://www.youtube.com/embed/...">
            <small class="form-text text-muted">Use apenas se quiser forçar uma URL de embed específica.</small>
        </div>

        <div class="form-row">
            <div class="form-group col-md-8 mb-3">
                <label for="thumbnail_url">URL da capa/thumbnail</label>
                <input type="url" name="thumbnail_url" id="thumbnail_url" value="{{ old('thumbnail_url', $lesson->thumbnail_url) }}" class="form-control" maxlength="1000">
            </div>
            <div class="form-group col-md-4 mb-3">
                <label for="duration_seconds">Duração em segundos</label>
                <input type="number" name="duration_seconds" id="duration_seconds" value="{{ old('duration_seconds', $lesson->duration_seconds) }}" class="form-control" min="1" max="86400" placeholder="Ex.: 480">
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="notes">Notas internas</label>
            <textarea name="notes" id="notes" rows="4" class="form-control" placeholder="Observações internas para equipe de conteúdo.">{{ old('notes', $lesson->notes) }}</textarea>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('admin.question-video-lessons.index') }}" class="btn btn-outline-secondary">Voltar</a>
    <button class="btn btn-primary">Salvar aula</button>
</div>
