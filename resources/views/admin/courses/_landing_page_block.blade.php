<div class="card mb-4 border-info" id="landing-page-config">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <div>
            <strong>Landing Page</strong>
            <div class="small opacity-75">
                Configure uma única página pública reutilizável para este curso.
            </div>
        </div>
        <span class="badge bg-light text-info">LP 2.0</span>
    </div>

    <div class="card-body">
        <div class="alert alert-light border mb-4">
            A página ficará disponível em
            <code>/preparatorio/{{ $course->slug ?: 'slug-do-curso' }}</code>
            quando o curso estiver ativo, público e com a publicação abaixo habilitada.
            Preços, ciclos de cobrança e trial continuam vindo dos dados comerciais do curso.
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">1. Publicação</h6>

            <div class="form-check form-switch">
                <input type="hidden" name="landing_enabled" value="0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="landing_enabled"
                    name="landing_enabled"
                    value="1"
                    @checked((bool) old('landing_enabled', $course->landing_enabled ?? false))
                >
                <label class="form-check-label" for="landing_enabled">
                    <strong>Publicar Landing Page</strong>
                </label>
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">2. Hero</h6>

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="landing_headline" class="form-label">Título principal</label>
                    <input
                        type="text"
                        name="landing_headline"
                        id="landing_headline"
                        class="form-control"
                        maxlength="180"
                        value="{{ old('landing_headline', $course->landing_headline ?? '') }}"
                        placeholder="Benefício principal para o candidato deste curso"
                    >
                </div>

                <div class="col-md-4">
                    <label for="landing_cta_text" class="form-label">Texto do CTA principal</label>
                    <input
                        type="text"
                        name="landing_cta_text"
                        id="landing_cta_text"
                        class="form-control"
                        maxlength="80"
                        value="{{ old('landing_cta_text', $course->landing_cta_text ?? '') }}"
                        placeholder="Começar a estudar"
                    >
                </div>

                <div class="col-12">
                    <label for="landing_subheadline" class="form-label">Subtítulo</label>
                    <textarea
                        name="landing_subheadline"
                        id="landing_subheadline"
                        class="form-control"
                        rows="2"
                        maxlength="500"
                        placeholder="Explique como o Papirar ajuda este candidato."
                    >{{ old('landing_subheadline', $course->landing_subheadline ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">3. Dor / problema</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="landing_problem_title" class="form-label">Título</label>
                    <input
                        type="text"
                        name="landing_problem_title"
                        id="landing_problem_title"
                        class="form-control"
                        maxlength="180"
                        value="{{ old('landing_problem_title', $course->landing_problem_title ?? '') }}"
                    >
                </div>

                <div class="col-md-6">
                    <label for="landing_problem_text" class="form-label">Texto</label>
                    <textarea
                        name="landing_problem_text"
                        id="landing_problem_text"
                        class="form-control"
                        rows="3"
                        maxlength="1000"
                    >{{ old('landing_problem_text', $course->landing_problem_text ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">4. Demonstração</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="landing_question_id" class="form-label">ID da questão demonstrativa</label>
                    <input
                        type="number"
                        min="1"
                        name="landing_question_id"
                        id="landing_question_id"
                        class="form-control"
                        value="{{ old('landing_question_id', $course->landing_question_id ?? '') }}"
                        placeholder="Ex.: 1004"
                    >
                    <div class="text-muted small mt-1">
                        A questão precisa estar visível ao aluno e pertencer ao escopo do curso.
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">5. Seções / funcionalidades</h6>

            @php
                $landingFeatureFlags = [
                    'landing_show_performance' => [
                        'Desempenho / diagnóstico',
                        'Explica como o aluno acompanha resultados e identifica onde precisa melhorar.'
                    ],
                    'landing_show_error_review' => [
                        'Revisão de erros',
                        'Mostra o benefício de voltar às questões que ainda precisam de atenção.'
                    ],
                    'landing_show_next_study' => [
                        'Recomendação / próximo estudo',
                        'Exibe o direcionamento de próximo estudo disponível no produto.'
                    ],
                    'landing_show_schedule' => [
                        'Cronograma',
                        'Exibe a seção de organização por cronograma.'
                    ],
                    'landing_show_goals' => [
                        'Metas',
                        'Exibe a seção de metas e constância.'
                    ],
                    'landing_show_simulations' => [
                        'Simulados',
                        'Apresenta o benefício de testar a preparação antes da prova.'
                    ],
                ];
            @endphp

            <div class="row g-3">
                @foreach($landingFeatureFlags as $field => [$label, $help])
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <div class="form-check form-switch mb-1">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="{{ $field }}"
                                    name="{{ $field }}"
                                    value="1"
                                    @checked((bool) old($field, $course->{$field} ?? false))
                                >
                                <label class="form-check-label font-weight-bold" for="{{ $field }}">
                                    {{ $label }}
                                </label>
                            </div>
                            <div class="text-muted small">{{ $help }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="landing-performance-image-group" class="mt-4">
                <label for="landing_performance_image" class="form-label font-weight-bold">
                    Screenshot da área de desempenho
                </label>

                <input
                    type="file"
                    name="landing_performance_image"
                    id="landing_performance_image"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div class="text-muted small mt-1">
                    Opcional. Use uma captura real do Papirar. Máximo 4 MB.
                </div>

                @if($course->landingPerformanceImageUrl())
                    <div class="mt-3">
                        <img
                            src="{{ $course->landingPerformanceImageUrl() }}"
                            alt="Imagem atual de desempenho da Landing Page"
                            style="width: 100%; max-width: 420px; border: 1px solid #e5e7eb; border-radius: 12px;"
                        >

                        <div class="form-check mt-2">
                            <input type="hidden" name="remove_landing_performance_image" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remove_landing_performance_image"
                                id="remove_landing_performance_image"
                                value="1"
                            >
                            <label class="form-check-label" for="remove_landing_performance_image">
                                Remover imagem atual
                            </label>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">6. Oferta</h6>

            <div class="alert alert-secondary mb-0">
                <strong>Fonte de verdade comercial:</strong><br>
                Trial:
                <strong>{{ $course->is_trial_available ? 'Ativo' : 'Inativo' }}</strong>
                @if($course->is_trial_available)
                    · {{ $course->trialDaysForAccess() }} dia(s)
                @endif
                <br>
                Mensal:
                <strong>R$ {{ number_format((float) ($course->price ?? 0), 2, ',', '.') }}</strong>

                @if($course->hasQuarterlyPrice())
                    · Trimestral:
                    <strong>R$ {{ number_format((float) $course->quarterly_price, 2, ',', '.') }}</strong>
                @endif

                @if($course->hasSemiannualPrice())
                    · Semestral:
                    <strong>R$ {{ number_format((float) $course->semiannual_price, 2, ',', '.') }}</strong>
                @endif

                <div class="text-muted small mt-2">
                    Para alterar esses dados, use “Dados comerciais”. Eles não são duplicados na Landing Page.
                </div>
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <h6 class="font-weight-bold mb-3">7. CTA final</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="landing_final_title" class="form-label">Título</label>
                    <input
                        type="text"
                        name="landing_final_title"
                        id="landing_final_title"
                        class="form-control"
                        maxlength="180"
                        value="{{ old('landing_final_title', $course->landing_final_title ?? '') }}"
                    >
                </div>

                <div class="col-md-6">
                    <label for="landing_final_cta_text" class="form-label">Texto do botão</label>
                    <input
                        type="text"
                        name="landing_final_cta_text"
                        id="landing_final_cta_text"
                        class="form-control"
                        maxlength="80"
                        value="{{ old('landing_final_cta_text', $course->landing_final_cta_text ?? '') }}"
                        placeholder="Deixe vazio para reutilizar o CTA principal"
                    >
                </div>

                <div class="col-12">
                    <label for="landing_final_text" class="form-label">Texto</label>
                    <textarea
                        name="landing_final_text"
                        id="landing_final_text"
                        class="form-control"
                        rows="2"
                        maxlength="500"
                    >{{ old('landing_final_text', $course->landing_final_text ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mb-4 pb-4 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h6 class="font-weight-bold mb-1">8. FAQ</h6>
                    <div class="text-muted small">
                        Perguntas específicas deste curso.
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-info" id="add-landing-faq">
                    Adicionar pergunta
                </button>
            </div>

            @php
                $faqRows = old('landing_faqs');

                if ($faqRows === null) {
                    $faqRows = ($landingFaqs ?? collect())
                        ->map(fn ($faq) => [
                            'id' => $faq->id,
                            'question' => $faq->question,
                            'answer' => $faq->answer,
                            'sort_order' => $faq->sort_order,
                            'is_active' => $faq->is_active ? 1 : 0,
                        ])
                        ->values()
                        ->all();
                }
            @endphp

            <div id="landing-faq-list">
                @foreach($faqRows as $faqIndex => $faq)
                    <div class="landing-faq-row border rounded p-3 mb-3 bg-light" data-faq-row>
                        <input
                            type="hidden"
                            name="landing_faqs[{{ $faqIndex }}][id]"
                            value="{{ $faq['id'] ?? '' }}"
                        >

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Pergunta</label>
                                <input
                                    type="text"
                                    name="landing_faqs[{{ $faqIndex }}][question]"
                                    class="form-control"
                                    maxlength="255"
                                    value="{{ $faq['question'] ?? '' }}"
                                >
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Ordem</label>
                                <input
                                    type="number"
                                    min="0"
                                    name="landing_faqs[{{ $faqIndex }}][sort_order]"
                                    class="form-control"
                                    value="{{ $faq['sort_order'] ?? ($faqIndex + 1) }}"
                                >
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input
                                        type="hidden"
                                        name="landing_faqs[{{ $faqIndex }}][is_active]"
                                        value="0"
                                    >

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="landing_faqs[{{ $faqIndex }}][is_active]"
                                        id="landing_faq_active_{{ $faqIndex }}"
                                        value="1"
                                        @checked((bool) ($faq['is_active'] ?? true))
                                    >

                                    <label
                                        class="form-check-label"
                                        for="landing_faq_active_{{ $faqIndex }}"
                                    >
                                        Ativo
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Resposta</label>
                                <textarea
                                    name="landing_faqs[{{ $faqIndex }}][answer]"
                                    class="form-control"
                                    rows="3"
                                    maxlength="5000"
                                >{{ $faq['answer'] ?? '' }}</textarea>
                            </div>

                            <div class="col-12 text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger js-remove-landing-faq"
                                >
                                    Remover
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <template id="landing-faq-template">
                <div class="landing-faq-row border rounded p-3 mb-3 bg-light" data-faq-row>
                    <input type="hidden" data-name="id" value="">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Pergunta</label>
                            <input type="text" data-name="question" class="form-control" maxlength="255">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Ordem</label>
                            <input type="number" min="0" data-name="sort_order" class="form-control">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" data-name="is_active_hidden" value="0">
                                <input class="form-check-input" type="checkbox" data-name="is_active" value="1" checked>
                                <label class="form-check-label">Ativo</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Resposta</label>
                            <textarea data-name="answer" class="form-control" rows="3" maxlength="5000"></textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-landing-faq">
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div>
            <h6 class="font-weight-bold mb-3">9. SEO</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="landing_seo_title" class="form-label">Título para Google</label>
                    <input
                        type="text"
                        name="landing_seo_title"
                        id="landing_seo_title"
                        class="form-control"
                        maxlength="70"
                        value="{{ old('landing_seo_title', $course->landing_seo_title ?? '') }}"
                    >
                </div>

                <div class="col-md-6">
                    <label for="landing_seo_description" class="form-label">Descrição para Google</label>
                    <textarea
                        name="landing_seo_description"
                        id="landing_seo_description"
                        class="form-control"
                        rows="2"
                        maxlength="170"
                    >{{ old('landing_seo_description', $course->landing_seo_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const faqList = document.getElementById('landing-faq-list');
    const addFaqButton = document.getElementById('add-landing-faq');
    const faqTemplate = document.getElementById('landing-faq-template');

    function bindRemoveButtons(scope) {
        scope.querySelectorAll('.js-remove-landing-faq').forEach(function (button) {
            if (button.dataset.bound === '1') return;

            button.dataset.bound = '1';

            button.addEventListener('click', function () {
                button.closest('[data-faq-row]')?.remove();
            });
        });
    }

    function nextFaqIndex() {
        const indexes = Array.from(
            faqList.querySelectorAll('[name^="landing_faqs["]')
        ).map(function (field) {
            const match = field.name.match(/^landing_faqs\[(\d+)\]/);
            return match ? Number(match[1]) : -1;
        });

        return indexes.length ? Math.max.apply(null, indexes) + 1 : 0;
    }

    if (faqList && addFaqButton && faqTemplate) {
        bindRemoveButtons(faqList);

        addFaqButton.addEventListener('click', function () {
            const index = nextFaqIndex();
            const fragment = faqTemplate.content.cloneNode(true);
            const row = fragment.querySelector('[data-faq-row]');

            row.querySelector('[data-name="id"]').name =
                `landing_faqs[${index}][id]`;

            row.querySelector('[data-name="question"]').name =
                `landing_faqs[${index}][question]`;

            row.querySelector('[data-name="answer"]').name =
                `landing_faqs[${index}][answer]`;

            row.querySelector('[data-name="sort_order"]').name =
                `landing_faqs[${index}][sort_order]`;

            row.querySelector('[data-name="sort_order"]').value = index + 1;

            row.querySelector('[data-name="is_active_hidden"]').name =
                `landing_faqs[${index}][is_active]`;

            const active = row.querySelector('[data-name="is_active"]');

            active.name = `landing_faqs[${index}][is_active]`;
            active.id = `landing_faq_active_${index}`;

            active.nextElementSibling?.setAttribute('for', active.id);

            faqList.appendChild(fragment);
            bindRemoveButtons(faqList);
        });
    }

    const performanceToggle = document.getElementById('landing_show_performance');
    const performanceImageGroup = document.getElementById('landing-performance-image-group');

    function syncPerformanceImageVisibility() {
        if (!performanceToggle || !performanceImageGroup) return;

        performanceImageGroup.style.display =
            performanceToggle.checked ? '' : 'none';
    }

    performanceToggle?.addEventListener(
        'change',
        syncPerformanceImageVisibility
    );

    syncPerformanceImageVisibility();
});
</script>
