<div class="card mb-4 border-primary" id="course-marketing-card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <strong>Card comercial do curso</strong>
            <div class="small opacity-75">Capa, descrição curta e textos usados nos cards de venda do aluno.</div>
        </div>
        <span class="badge badge-light bg-light text-primary">Área comercial</span>
    </div>

    <div class="card-body">
        <div class="alert alert-light border mb-4">
            <strong>Onde aparece:</strong> estes campos alimentam os cards em <code>/aluno/cursos</code>, <code>/aluno/assinaturas</code> e a vitrine da dashboard quando o aluno ainda não comprou curso.
        </div>

        <div class="row g-3">
            <div class="col-md-5">
                <label for="cover_image" class="form-label font-weight-bold">Imagem/capa do card do curso</label>
                <input type="file" name="cover_image" id="cover_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="text-muted small mt-1">Formatos: JPG, PNG ou WEBP. Recomendado: 1200x675 ou 16:9. Tamanho máximo: 4 MB.</div>

                @if($course->coverImageUrl())
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Capa atual:</div>
                        <img src="{{ $course->coverImageUrl() }}" alt="Capa do curso" style="width: 100%; max-width: 360px; aspect-ratio: 16/9; object-fit: cover; border-radius: 14px; border: 1px solid #e5e7eb;">
                        <div class="form-check mt-2">
                            <input type="hidden" name="remove_cover_image" value="0">
                            <input type="checkbox" name="remove_cover_image" id="remove_cover_image" value="1" class="form-check-input">
                            <label for="remove_cover_image" class="form-check-label">Remover capa atual</label>
                        </div>
                    </div>
                @else
                    <div class="mt-3 p-4 rounded border text-center bg-light">
                        <div class="font-weight-bold">Sem capa cadastrada</div>
                        <div class="text-muted small">Enquanto não houver imagem, o card exibirá um placeholder do Papirar.</div>
                    </div>
                @endif
            </div>

            <div class="col-md-7">
                <div class="mb-3">
                    <label for="short_description" class="form-label font-weight-bold">Descrição curta do card</label>
                    <textarea name="short_description" id="short_description" class="form-control" rows="3" maxlength="255" placeholder="Ex.: Curso completo para treinar questões do CHOAE CBMERJ com simulados, comentários e acompanhamento de desempenho.">{{ old('short_description', $course->short_description ?? '') }}</textarea>
                    <div class="text-muted small mt-1">Texto principal do card. Use até 255 caracteres.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="sales_badge" class="form-label">Selo de destaque</label>
                        <input type="text" name="sales_badge" id="sales_badge" class="form-control" value="{{ old('sales_badge', $course->sales_badge ?? '') }}" maxlength="80" placeholder="Ex.: Mais procurado">
                    </div>

                    <div class="col-md-6">
                        <label for="workload_label" class="form-label">Resumo do conteúdo</label>
                        <input type="text" name="workload_label" id="workload_label" class="form-control" value="{{ old('workload_label', $course->workload_label ?? '') }}" maxlength="80" placeholder="Ex.: 12 disciplinas • 1.200 questões">
                    </div>
                </div>

                <div class="mt-3">
                    <label for="sales_headline" class="form-label">Chamada comercial</label>
                    <input type="text" name="sales_headline" id="sales_headline" class="form-control" value="{{ old('sales_headline', $course->sales_headline ?? '') }}" maxlength="180" placeholder="Ex.: Treine com foco no seu concurso interno.">
                    <div class="text-muted small mt-1">Quando preenchida, pode aparecer com mais destaque que a descrição curta.</div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label for="target_audience" class="form-label">Público-alvo</label>
                        <input type="text" name="target_audience" id="target_audience" class="form-control" value="{{ old('target_audience', $course->target_audience ?? '') }}" maxlength="180" placeholder="Ex.: Praças do CBMERJ que vão disputar o CHOAE">
                    </div>

                    <div class="col-md-6">
                        <label for="guarantee_text" class="form-label">Texto de confiança</label>
                        <input type="text" name="guarantee_text" id="guarantee_text" class="form-control" value="{{ old('guarantee_text', $course->guarantee_text ?? '') }}" maxlength="180" placeholder="Ex.: Acesso imediato após confirmação do pagamento">
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label for="sales_bullets_text" class="form-label">Benefícios em destaque</label>
                <textarea name="sales_bullets_text" id="sales_bullets_text" class="form-control" rows="5" placeholder="Um benefício por linha">{{ old('sales_bullets_text', $course->salesBulletsText()) }}</textarea>
                <div class="text-muted small mt-1">Use um benefício por linha. Ex.: Questões comentadas; Simulados por curso; Favoritas com anotações; Aulas por questão quando houver.</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 border-info" id="landing-page-v2-settings">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <div>
            <strong>Landing Page 2.0 — recursos e FAQ</strong>
            <div class="small opacity-75">
                Infraestrutura das novas seções. Hero, dor, questão demonstrativa e SEO continuam no bloco “Landing page de campanha”.
            </div>
        </div>
        <span class="badge bg-light text-info">LP 2.0</span>
    </div>

    <div class="card-body">
        <div class="alert alert-light border">
            <strong>Oferta:</strong> preço, ciclos de cobrança, disponibilidade de trial e quantidade de dias continuam usando os campos comerciais do próprio curso.
            Não há preço ou trial duplicado na Landing Page.
        </div>

        <h6 class="font-weight-bold mb-3">Seções e funcionalidades</h6>

        <div class="row g-3">
            @php
                $landingFeatureFlags = [
                    'landing_show_performance' => ['Desempenho / diagnóstico', 'Explica como o aluno acompanha resultados e identifica onde precisa melhorar.'],
                    'landing_show_error_review' => ['Revisão de erros', 'Apresenta o benefício de voltar às questões que ainda precisam de atenção.'],
                    'landing_show_next_study' => ['Recomendação / próximo estudo', 'Exibe a seção de direcionamento do próximo estudo quando apropriado ao curso.'],
                    'landing_show_schedule' => ['Cronograma', 'Exibe a seção de organização por cronograma.'],
                    'landing_show_goals' => ['Metas', 'Exibe a seção de metas e constância.'],
                    'landing_show_simulations' => ['Simulados', 'Apresenta o benefício de testar a preparação antes da prova.'],
                ];
            @endphp

            @foreach($landingFeatureFlags as $field => [$label, $help])
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="form-check form-switch mb-1">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input
                                class="form-check-input js-landing-feature-flag"
                                type="checkbox"
                                role="switch"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                value="1"
                                @checked((bool) old($field, $course->{$field} ?? false))
                            >
                            <label class="form-check-label font-weight-bold" for="{{ $field }}">{{ $label }}</label>
                        </div>
                        <div class="text-muted small">{{ $help }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <hr class="my-4">

        <div id="landing-performance-image-group">
            <h6 class="font-weight-bold mb-2">Imagem da área de desempenho</h6>
            <p class="text-muted small">
                Screenshot opcional do próprio Papirar para apoiar visualmente a seção de diagnóstico. Não use arte que prometa métricas inexistentes.
            </p>

            <div class="row g-3 align-items-start">
                <div class="col-md-6">
                    <input
                        type="file"
                        name="landing_performance_image"
                        id="landing_performance_image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <div class="text-muted small mt-1">JPG, PNG ou WEBP. Máximo 4 MB.</div>
                </div>

                <div class="col-md-6">
                    @if($course->landingPerformanceImageUrl())
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
                            <label class="form-check-label" for="remove_landing_performance_image">Remover imagem atual</label>
                        </div>
                    @else
                        <div class="border rounded p-3 bg-light text-muted small">
                            Nenhuma imagem de desempenho cadastrada.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="font-weight-bold mb-3">CTA final</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="landing_final_cta_text" class="form-label">Texto do botão final</label>
                <input
                    type="text"
                    name="landing_final_cta_text"
                    id="landing_final_cta_text"
                    class="form-control"
                    maxlength="80"
                    value="{{ old('landing_final_cta_text', $course->landing_final_cta_text ?? '') }}"
                    placeholder="Deixe vazio para reutilizar o CTA principal"
                >
                <div class="text-muted small mt-1">
                    Opcional. O título e o texto da chamada final continuam no bloco “Landing page de campanha”.
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h6 class="font-weight-bold mb-1">FAQ específico do curso</h6>
                <div class="text-muted small">Perguntas estruturadas, ordenáveis e ativáveis individualmente.</div>
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
                    <input type="hidden" name="landing_faqs[{{ $faqIndex }}][id]" value="{{ $faq['id'] ?? '' }}">

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
                                <input type="hidden" name="landing_faqs[{{ $faqIndex }}][is_active]" value="0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="landing_faqs[{{ $faqIndex }}][is_active]"
                                    id="landing_faq_active_{{ $faqIndex }}"
                                    value="1"
                                    @checked((bool) ($faq['is_active'] ?? true))
                                >
                                <label class="form-check-label" for="landing_faq_active_{{ $faqIndex }}">Ativo</label>
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
                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-landing-faq">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const faqList = document.getElementById('landing-faq-list');
    const addFaqButton = document.getElementById('add-landing-faq');
    const faqTemplate = document.getElementById('landing-faq-template');

    function bindRemoveButtons(scope) {
        scope.querySelectorAll('.js-remove-landing-faq').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }

            button.dataset.bound = '1';
            button.addEventListener('click', function () {
                button.closest('[data-faq-row]')?.remove();
            });
        });
    }

    function nextFaqIndex() {
        const indexes = Array.from(faqList.querySelectorAll('[name^="landing_faqs["]'))
            .map(function (field) {
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

            row.querySelector('[data-name="id"]').name = `landing_faqs[${index}][id]`;
            row.querySelector('[data-name="question"]').name = `landing_faqs[${index}][question]`;
            row.querySelector('[data-name="answer"]').name = `landing_faqs[${index}][answer]`;
            row.querySelector('[data-name="sort_order"]').name = `landing_faqs[${index}][sort_order]`;
            row.querySelector('[data-name="sort_order"]').value = index + 1;
            row.querySelector('[data-name="is_active_hidden"]').name = `landing_faqs[${index}][is_active]`;

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
        if (!performanceToggle || !performanceImageGroup) {
            return;
        }

        performanceImageGroup.style.display = performanceToggle.checked ? '' : 'none';
    }

    performanceToggle?.addEventListener('change', syncPerformanceImageVisibility);
    syncPerformanceImageVisibility();
});
</script>
