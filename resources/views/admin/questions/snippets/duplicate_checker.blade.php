@php
    $duplicateQuestionId = isset($question) && $question?->exists ? $question->id : null;
@endphp

<div class="card border-warning mb-3" id="question-duplicate-checker" data-question-id="{{ $duplicateQuestionId }}">
    <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
        <strong>Controle de duplicidade</strong>
        <button type="button" class="btn btn-sm btn-outline-dark" id="btn-check-question-duplicate">
            Verificar duplicidade
        </button>
    </div>
    <div class="card-body py-2">
        <p class="mb-2 small text-muted">
            A verificação compara o enunciado normalizado e identifica questões com o mesmo texto já cadastrado.
        </p>
        <div id="question-duplicate-result" class="small"></div>
    </div>
</div>
