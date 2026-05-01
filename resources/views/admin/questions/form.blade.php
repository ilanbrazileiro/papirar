<div class="card-soft p-4">
    <h1 class="page-title mb-4">{{ $question ? 'Editar' : 'Nova' }} questão</h1>
    <form method="POST" action="{{ $action }}">@csrf @if ($method !== 'POST')
            @method($method)
        @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Corporação</label><select class="form-select"
                    name="corporation_id">
                    <option value="">Questão geral / sem corporação específica</option>
                    @foreach ($corporations as $item)
                        <option value="{{ $item->id }}" @selected(old('corporation_id', $question->corporation_id ?? null) == $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Prova</label><select class="form-select" name="exam_id">
                    <option value="">Selecione</option>
                    @foreach ($exams as $item)
                        <option value="{{ $item->id }}" @selected(old('exam_id', $question->exam_id ?? null) == $item->id)>{{ $item->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Disciplina</label><select class="form-select"
                    name="subject_id" required>
                    @foreach ($subjects as $item)
                        <option value="{{ $item->id }}" @selected(old('subject_id', $question->subject_id ?? null) == $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Assunto</label><select class="form-select" name="topic_id">
                    <option value="">Selecione</option>
                    @foreach ($topics as $item)
                        <option value="{{ $item->id }}" @selected(old('topic_id', $question->topic_id ?? null) == $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Tipo</label><input class="form-control" name="question_type"
                    value="{{ old('question_type', $question->question_type ?? 'multiple_choice') }}" required></div>
            <div class="col-md-4"><label class="form-label">Dificuldade</label><select class="form-select"
                    name="difficulty" required>
                    <option value="easy" @selected(old('difficulty', $question->difficulty ?? '') === 'easy')>Fácil</option>
                    <option value="medium" @selected(old('difficulty', $question->difficulty ?? '') === 'medium')>Média</option>
                    <option value="hard" @selected(old('difficulty', $question->difficulty ?? '') === 'hard')>Difícil</option>
                </select></div>
            <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"
                    required>
                    <option value="draft" @selected(old('status', $question->status ?? '') === 'draft')>Rascunho</option>
                    <option value="published" @selected(old('status', $question->status ?? '') === 'published')>Publicada</option>
                    <option value="archived" @selected(old('status', $question->status ?? '') === 'archived')>Arquivada</option>
                </select></div>
            <div class="col-md-6"><label class="form-label">Origem</label><input class="form-control" name="source_type"
                    value="{{ old('source_type', $question->source_type ?? 'official_exam') }}" required></div>
            <div class="col-md-6"><label class="form-label">Referência</label><input class="form-control"
                    name="source_reference" value="{{ old('source_reference', $question->source_reference ?? '') }}">
            </div>
            <div class="col-12"><label class="form-label">Enunciado</label>
                <textarea class="form-control" name="statement" rows="6" required>{{ old('statement', $question->statement ?? '') }}</textarea>
            </div>
            <div class="col-12"><label class="form-label">Comentário oficial</label>
                <textarea class="form-control" name="commented_answer" rows="5">{{ old('commented_answer', $question->commented_answer ?? '') }}</textarea>
            </div>
            <div class="col-12"><label class="form-label">Alternativas</label>@php($alternatives = old('alternatives', isset($question) && $question ? $question->alternatives->map(fn($a) => ['letter' => $a->letter, 'text' => $a->text, 'is_correct' => $a->is_correct])->toArray() : [['letter' => 'A', 'text' => '', 'is_correct' => false], ['letter' => 'B', 'text' => '', 'is_correct' => false], ['letter' => 'C', 'text' => '', 'is_correct' => false], ['letter' => 'D', 'text' => '', 'is_correct' => false]])) @foreach ($alternatives as $i => $alt)
                    <div class="row g-2 mb-2">
                        <div class="col-md-1"><input class="form-control"
                                name="alternatives[{{ $i }}][letter]" value="{{ $alt['letter'] ?? '' }}"
                                required></div>
                        <div class="col-md-9"><input class="form-control"
                                name="alternatives[{{ $i }}][text]" value="{{ $alt['text'] ?? '' }}"
                                required></div>
                        <div class="col-md-2 d-flex align-items-center">
                            <div class="form-check"><input class="form-check-input" type="checkbox"
                                    name="alternatives[{{ $i }}][is_correct]" value="1"
                                    @checked(!empty($alt['is_correct']))><label class="form-check-label">Correta</label></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div><button class="btn btn-primary mt-4">Salvar</button>
    </form>
</div>
