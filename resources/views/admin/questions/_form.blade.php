@php
    $letters = ['A', 'B', 'C', 'D', 'E'];
    $alts = $question->alternatives->sortBy('letter')->values();
    $correctLetter = old('correct_letter', optional($question->alternatives->firstWhere('is_correct', true))->letter ?? 'A');
@endphp

<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($formMethod ?? 'POST') !== 'POST')
        @method($formMethod)
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Enunciado da questão
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i>
                        Para inserir imagem como no WordPress, clique no ícone de imagem do editor, selecione o arquivo e aguarde o upload.
                        A imagem será salva no Storage e inserida no enunciado automaticamente.
                    </div>

                    <div class="form-group">
                        <label for="statement">Enunciado <span class="text-danger">*</span></label>
                        <textarea
                            name="statement"
                            id="statement"
                            class="form-control rich-editor @error('statement') is-invalid @enderror"
                            rows="12"
                            data-editor-height="420"
                            data-upload-url="{{ route('admin.editor-images.upload') }}"
                        >{{ old('statement', $question->statement) }}</textarea>
                        @error('statement')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-ol"></i> Alternativas
                    </h3>
                </div>
                <div class="card-body">
                    @foreach($letters as $index => $letter)
                        @php
                            $alt = $alts->firstWhere('letter', $letter);
                            $oldText = old("alternatives.$index.text", $alt->text ?? '');
                            $oldLetter = old("alternatives.$index.letter", $letter);
                        @endphp

                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="row align-items-start">
                                <div class="col-md-2">
                                    <label>Letra</label>
                                    <input type="text" name="alternatives[{{ $index }}][letter]" class="form-control text-center font-weight-bold" value="{{ $oldLetter }}" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label>Texto da alternativa {{ $letter }} <span class="text-danger">*</span></label>
                                    <textarea name="alternatives[{{ $index }}][text]" class="form-control @error("alternatives.$index.text") is-invalid @enderror" rows="3">{{ $oldText }}</textarea>
                                    @error("alternatives.$index.text")
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label>Correta?</label>
                                    <div class="custom-control custom-radio mt-2">
                                        <input type="radio" id="correct_{{ $letter }}" name="correct_letter" value="{{ $letter }}" class="custom-control-input" {{ $correctLetter === $letter ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="correct_{{ $letter }}">{{ $letter }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @error('correct_letter')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comments"></i> Comentário / gabarito comentado
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="commented_answer">Comentário</label>
                        <textarea
                            name="commented_answer"
                            id="commented_answer"
                            class="form-control rich-editor @error('commented_answer') is-invalid @enderror"
                            rows="8"
                            data-editor-height="320"
                            data-upload-url="{{ route('admin.editor-images.upload') }}"
                        >{{ old('commented_answer', $question->commented_answer) }}</textarea>
                        @error('commented_answer')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-primary sticky-top" style="top: 1rem;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Configurações</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="corporation_id">Corporação</label>
                        <select name="corporation_id" id="corporation_id" class="form-control @error('corporation_id') is-invalid @enderror">
                            <option value="">Questão geral / sem corporação específica</option>
                            @foreach($corporations as $corporation)
                                <option value="{{ $corporation->id }}" {{ (string) old('corporation_id', $question->corporation_id) === (string) $corporation->id ? 'selected' : '' }}>
                                    {{ $corporation->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('corporation_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="subject_id">Disciplina <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-control @error('subject_id') is-invalid @enderror">
                            <option value="">Selecione</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) old('subject_id', $question->subject_id) === (string) $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="topic_id">Assunto</label>
                        <select name="topic_id" id="topic_id" class="form-control @error('topic_id') is-invalid @enderror">
                            <option value="{{ old('topic_id', $question->topic_id) }}">
                                @if(old('topic_id', $question->topic_id) && $selectedTopic)
                                    {{ $selectedTopic->name }}
                                @else
                                    Selecione uma disciplina primeiro
                                @endif
                            </option>
                        </select>
                        <small class="form-text text-muted">Busca dinâmica por disciplina.</small>
                        @error('topic_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="exam_id">Concurso / prova de origem</label>
                        <select name="exam_id" id="exam_id" class="form-control @error('exam_id') is-invalid @enderror">
                            <option value="{{ old('exam_id', $question->exam_id) }}">
                                @if(old('exam_id', $question->exam_id) && $selectedExam)
                                    {{ $selectedExam->title }} ({{ $selectedExam->year }}) - {{ $selectedExam->exam_type }}
                                @else
                                    Sem prova de origem
                                @endif
                            </option>
                        </select>
                        <small class="form-text text-muted">Use apenas quando a questão veio de prova oficial.</small>
                        @error('exam_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="difficulty">Dificuldade</label>
                        <select name="difficulty" id="difficulty" class="form-control">
                            <option value="easy" {{ old('difficulty', $question->difficulty) === 'easy' ? 'selected' : '' }}>Fácil</option>
                            <option value="medium" {{ old('difficulty', $question->difficulty) === 'medium' ? 'selected' : '' }}>Média</option>
                            <option value="hard" {{ old('difficulty', $question->difficulty) === 'hard' ? 'selected' : '' }}>Difícil</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="source_type">Origem</label>
                        <select name="source_type" id="source_type" class="form-control">
                            <option value="exam" {{ old('source_type', $question->source_type) === 'exam' ? 'selected' : '' }}>Prova oficial</option>
                            <option value="authored" {{ old('source_type', $question->source_type) === 'authored' ? 'selected' : '' }}>Autoral</option>
                            <option value="adapted" {{ old('source_type', $question->source_type) === 'adapted' ? 'selected' : '' }}>Adaptada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="draft" {{ old('status', $question->status) === 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="published" {{ old('status', $question->status) === 'published' ? 'selected' : '' }}>Publicada</option>
                            <option value="archived" {{ old('status', $question->status) === 'archived' ? 'selected' : '' }}>Arquivada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="source_reference">Referência da fonte</label>
                        <input type="text" name="source_reference" id="source_reference" class="form-control" value="{{ old('source_reference', $question->source_reference) }}" placeholder="Ex.: CHOE PMERJ 2022 - Q15">
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ $submitLabel }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 280px;
    }
    #commented_answer + .ck-editor .ck-editor__editable_inline {
        min-height: 220px;
    }
    .ck-content img {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
(function () {
    class PapirarUploadAdapter {
        constructor(loader, uploadUrl, csrfToken) {
            this.loader = loader;
            this.uploadUrl = uploadUrl;
            this.csrfToken = csrfToken;
            this.xhr = null;
        }

        upload() {
            return this.loader.file.then(file => new Promise((resolve, reject) => {
                this._initRequest();
                this._initListeners(resolve, reject, file);
                this._sendRequest(file);
            }));
        }

        abort() {
            if (this.xhr) {
                this.xhr.abort();
            }
        }

        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest();
            xhr.open('POST', this.uploadUrl, true);
            xhr.responseType = 'json';
            xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');
        }

        _initListeners(resolve, reject, file) {
            const xhr = this.xhr;
            const loader = this.loader;
            const genericErrorText = `Não foi possível enviar a imagem: ${file.name}.`;

            xhr.addEventListener('error', () => reject(genericErrorText));
            xhr.addEventListener('abort', () => reject());
            xhr.addEventListener('load', () => {
                const response = xhr.response;

                if (!response || xhr.status < 200 || xhr.status >= 300) {
                    const message = response?.message || response?.errors?.upload?.[0] || genericErrorText;
                    return reject(message);
                }

                if (!response.url) {
                    return reject('Upload concluído, mas a URL da imagem não foi retornada.');
                }

                resolve({ default: response.url });
            });

            if (xhr.upload) {
                xhr.upload.addEventListener('progress', evt => {
                    if (evt.lengthComputable) {
                        loader.uploadTotal = evt.total;
                        loader.uploaded = evt.loaded;
                    }
                });
            }
        }

        _sendRequest(file) {
            const data = new FormData();
            data.append('upload', file);
            this.xhr.send(data);
        }
    }

    function PapirarUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = loader => {
            const element = editor.sourceElement;
            const uploadUrl = element.dataset.uploadUrl;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            return new PapirarUploadAdapter(loader, uploadUrl, csrfToken);
        };
    }

    document.querySelectorAll('textarea.rich-editor').forEach((textarea) => {
        ClassicEditor
            .create(textarea, {
                extraPlugins: [PapirarUploadAdapterPlugin],
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', 'imageUpload', '|',
                    'undo', 'redo'
                ],
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'toggleImageCaption',
                        'imageStyle:inline',
                        'imageStyle:block',
                        'imageStyle:side'
                    ]
                },
                table: {
                    contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                }
            })
            .then(editor => {
                const height = textarea.dataset.editorHeight;
                if (height) {
                    editor.editing.view.change(writer => {
                        writer.setStyle('min-height', `${height}px`, editor.editing.view.document.getRoot());
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar CKEditor:', error);
            });
    });
})();
</script>
@endpush
