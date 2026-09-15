@php
    $landingPublished = (bool) ($course->landing_enabled ?? false);

    $landingPubliclyAvailable = $landingPublished
        && (bool) ($course->active ?? false)
        && (bool) ($course->is_public ?? false)
        && filled($course->slug);

    $landingFeatureFlags = [
        'landing_show_performance' => [
            'Desempenho / diagnóstico',
            'Mostra como o aluno acompanha resultados e identifica onde precisa melhorar.',
        ],
        'landing_show_error_review' => [
            'Revisão de erros',
            'Apresenta a revisão das questões que ainda precisam de atenção.',
        ],
        'landing_show_next_study' => [
            'Próximo estudo',
            'Mostra o direcionamento do próximo estudo disponível no produto.',
        ],
        'landing_show_schedule' => [
            'Cronograma',
            'Apresenta os recursos de organização por cronograma.',
        ],
        'landing_show_goals' => [
            'Metas',
            'Apresenta metas e constância de estudo.',
        ],
        'landing_show_simulations' => [
            'Simulados',
            'Mostra o uso de simulados dentro do curso.',
        ],
    ];

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

<div class="card card-outline card-info mb-4" id="landing-page-config">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-md-0">
                <h3 class="card-title font-weight-bold mb-1">Landing Page</h3>
                <div class="text-muted small">
                    Configure a página pública de conversão deste curso.
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap">
                @if($landingPubliclyAvailable)
                    <span class="badge badge-success mr-2 mb-1">Publicada</span>
                @elseif($landingPublished)
                    <span class="badge badge-warning mr-2 mb-1">Publicação incompleta</span>
                @else
                    <span class="badge badge-secondary mr-2 mb-1">Não publicada</span>
                @endif

                @if($course->exists && $course->slug && $landingPubliclyAvailable)
                    <a
                        href="{{ route('site.course-landings.show', ['slug' => $course->slug]) }}"
                        class="btn btn-sm btn-outline-info mb-1"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Visualizar
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-light border">
            <strong>Uma única LP por curso.</strong>
            O conteúdo abaixo controla
            <code>/preparatorio/{{ $course->slug ?: 'slug-do-curso' }}</code>.
            Preço, ciclos de cobrança e trial continuam vindo dos dados comerciais do curso.
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="landing-admin-summary">
                    <span class="landing-admin-label">Status</span>
                    <strong>
                        @if($landingPubliclyAvailable)
                            Landing disponível
                        @elseif($landingPublished)
                            Publicação incompleta
                        @else
                            Landing desativada
                        @endif
                    </strong>
                </div>
            </div>

            <div class="col-md-4 mb-3 mb-md-0">
                <div class="landing-admin-summary">
                    <span class="landing-admin-label">Trial usado na LP</span>
                    <strong>
                        @if($course->is_trial_available)
                            {{ $course->trialDaysForAccess() }} dia(s)
                        @else
                            Sem trial
                        @endif
                    </strong>
                </div>
            </div>

            <div class="col-md-4">
                <div class="landing-admin-summary">
                    <span class="landing-admin-label">Preço comercial</span>
                    <strong>{{ $course->bestCommercialPriceLabel() }}</strong>
                </div>
            </div>
        </div>

        <div id="landing-config-accordion">
            {{-- 1. Publicação e Hero --}}
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header p-0" id="landing-heading-publicacao">
                    <button
                        type="button"
                        class="btn btn-block text-left landing-admin-collapse-btn"
                        data-toggle="collapse"
                        data-target="#landing-section-publicacao"
                        aria-expanded="true"
                        aria-controls="landing-section-publicacao"
                    >
                        <span>1. Publicação e Hero</span>
                        <i class="fas fa-chevron-up landing-collapse-icon"></i>
                    </button>
                </div>

                <div
                    id="landing-section-publicacao"
                    class="collapse show"
                    aria-labelledby="landing-heading-publicacao"
                    data-parent="#landing-config-accordion"
                >
                    <div class="card-body">
                        <div class="custom-control custom-switch mb-4">
                            <input type="hidden" name="landing_enabled" value="0">
                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="landing_enabled"
                                name="landing_enabled"
                                value="1"
                                @checked((bool) old('landing_enabled', $course->landing_enabled ?? false))
                            >
                            <label class="custom-control-label font-weight-bold" for="landing_enabled">
                                Publicar Landing Page
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="landing_headline">
                                        Título principal
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <input
                                        type="text"
                                        name="landing_headline"
                                        id="landing_headline"
                                        class="form-control"
                                        maxlength="180"
                                        value="{{ old('landing_headline', $course->landing_headline ?? '') }}"
                                        placeholder="Benefício principal para o candidato"
                                    >
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="landing_cta_text">
                                        CTA principal
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
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
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="landing_subheadline">
                                        Subtítulo
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <textarea
                                        name="landing_subheadline"
                                        id="landing_subheadline"
                                        class="form-control"
                                        rows="2"
                                        maxlength="500"
                                        placeholder="Explique de forma direta como o Papirar ajuda este candidato."
                                    >{{ old('landing_subheadline', $course->landing_subheadline ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Dor e demonstração --}}
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header p-0" id="landing-heading-problema">
                    <button
                        type="button"
                        class="btn btn-block text-left landing-admin-collapse-btn collapsed"
                        data-toggle="collapse"
                        data-target="#landing-section-problema"
                        aria-expanded="false"
                        aria-controls="landing-section-problema"
                    >
                        <span>2. Dor e demonstração</span>
                        <i class="fas fa-chevron-down landing-collapse-icon"></i>
                    </button>
                </div>

                <div
                    id="landing-section-problema"
                    class="collapse"
                    aria-labelledby="landing-heading-problema"
                    data-parent="#landing-config-accordion"
                >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="landing_problem_title">
                                        Título da dor
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <input
                                        type="text"
                                        name="landing_problem_title"
                                        id="landing_problem_title"
                                        class="form-control"
                                        maxlength="180"
                                        value="{{ old('landing_problem_title', $course->landing_problem_title ?? '') }}"
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="landing_question_id">
                                        ID da questão demonstrativa
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        name="landing_question_id"
                                        id="landing_question_id"
                                        class="form-control"
                                        value="{{ old('landing_question_id', $course->landing_question_id ?? '') }}"
                                        placeholder="Ex.: 1004"
                                    >
                                    <small class="form-text text-muted">
                                        Precisa estar visível ao aluno e pertencer ao escopo do curso.
                                    </small>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="landing_problem_text">
                                        Texto da dor
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
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
                    </div>
                </div>
            </div>

            {{-- 3. Recursos --}}
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header p-0" id="landing-heading-recursos">
                    <button
                        type="button"
                        class="btn btn-block text-left landing-admin-collapse-btn collapsed"
                        data-toggle="collapse"
                        data-target="#landing-section-recursos"
                        aria-expanded="false"
                        aria-controls="landing-section-recursos"
                    >
                        <span>3. Recursos exibidos na LP</span>
                        <i class="fas fa-chevron-down landing-collapse-icon"></i>
                    </button>
                </div>

                <div
                    id="landing-section-recursos"
                    class="collapse"
                    aria-labelledby="landing-heading-recursos"
                    data-parent="#landing-config-accordion"
                >
                    <div class="card-body">
                        <div class="row">
                            @foreach($landingFeatureFlags as $field => [$label, $help])
                                <div class="col-md-6 mb-3">
                                    <div class="landing-admin-feature h-100">
                                        <div class="custom-control custom-switch mb-2">
                                            <input type="hidden" name="{{ $field }}" value="0">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                id="{{ $field }}"
                                                name="{{ $field }}"
                                                value="1"
                                                @checked((bool) old($field, $course->{$field} ?? false))
                                            >
                                            <label class="custom-control-label font-weight-bold" for="{{ $field }}">
                                                {{ $label }}
                                            </label>
                                        </div>

                                        <div class="text-muted small">
                                            {{ $help }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="landing-performance-image-group" class="mt-3">
                            <div class="form-group mb-0">
                                <label for="landing_performance_image">
                                    Screenshot da área de desempenho
                                    <small class="text-muted font-weight-normal">(opcional)</small>
                                </label>

                                <input
                                    type="file"
                                    name="landing_performance_image"
                                    id="landing_performance_image"
                                    class="form-control-file"
                                    accept="image/jpeg,image/png,image/webp"
                                >

                                <small class="form-text text-muted">
                                    Use uma captura real da plataforma. Máximo 4 MB.
                                </small>

                                @if($course->landingPerformanceImageUrl())
                                    <div class="mt-3">
                                        <img
                                            src="{{ $course->landingPerformanceImageUrl() }}"
                                            alt="Imagem atual de desempenho da Landing Page"
                                            class="img-fluid landing-admin-preview-image"
                                        >

                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="hidden" name="remove_landing_performance_image" value="0">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                name="remove_landing_performance_image"
                                                id="remove_landing_performance_image"
                                                value="1"
                                            >
                                            <label
                                                class="custom-control-label"
                                                for="remove_landing_performance_image"
                                            >
                                                Remover imagem atual
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Oferta --}}
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header p-0" id="landing-heading-oferta">
                    <button
                        type="button"
                        class="btn btn-block text-left landing-admin-collapse-btn collapsed"
                        data-toggle="collapse"
                        data-target="#landing-section-oferta"
                        aria-expanded="false"
                        aria-controls="landing-section-oferta"
                    >
                        <span>4. Oferta e CTA final</span>
                        <i class="fas fa-chevron-down landing-collapse-icon"></i>
                    </button>
                </div>

                <div
                    id="landing-section-oferta"
                    class="collapse"
                    aria-labelledby="landing-heading-oferta"
                    data-parent="#landing-config-accordion"
                >
                    <div class="card-body">
                        <div class="alert alert-secondary">
                            <strong>Dados comerciais usados automaticamente:</strong><br>
                            Trial:
                            <strong>{{ $course->is_trial_available ? 'Ativo' : 'Inativo' }}</strong>

                            @if($course->is_trial_available)
                                · {{ $course->trialDaysForAccess() }} dia(s)
                            @endif

                            <br>
                            {{ $course->bestCommercialPriceLabel() }}

                            <div class="text-muted small mt-2">
                                Para alterar preço ou trial, edite “Dados comerciais”.
                                A LP não duplica esses dados.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="landing_final_title">
                                        Título final
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <input
                                        type="text"
                                        name="landing_final_title"
                                        id="landing_final_title"
                                        class="form-control"
                                        maxlength="180"
                                        value="{{ old('landing_final_title', $course->landing_final_title ?? '') }}"
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="landing_final_cta_text">
                                        CTA final
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <input
                                        type="text"
                                        name="landing_final_cta_text"
                                        id="landing_final_cta_text"
                                        class="form-control"
                                        maxlength="80"
                                        value="{{ old('landing_final_cta_text', $course->landing_final_cta_text ?? '') }}"
                                        placeholder="Vazio = reutiliza o CTA principal"
                                    >
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label for="landing_final_text">
                                        Texto final
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
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
                    </div>
                </div>
            </div>

            {{-- 5. FAQ --}}
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header p-0" id="landing-heading-faq">
                    <button
                        type="button"
                        class="btn btn-block text-left landing-admin-collapse-btn collapsed"
                        data-toggle="collapse"
                        data-target="#landing-section-faq"
                        aria-expanded="false"
                        aria-controls="landing-section-faq"
                    >
                        <span>
                            5. FAQ
                            @if(count($faqRows))
                                <span class="badge badge-secondary ml-1">{{ count($faqRows) }}</span>
                            @endif
                        </span>
                        <i class="fas fa-chevron-down landing-collapse-icon"></i>
                    </button>
                </div>

                <div
                    id="landing-section-faq"
                    class="collapse"
                    aria-labelledby="landing-heading-faq"
                    data-parent="#landing-config-accordion"
                >
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                            <div class="text-muted small mb-2 mb-md-0">
                                Cadastre apenas dúvidas realmente úteis para conversão.
                            </div>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-info"
                                id="add-landing-faq"
                            >
                                <i class="fas fa-plus mr-1"></i>
                                Adicionar pergunta
                            </button>
                        </div>

                        <div id="landing-faq-list">
                            @foreach($faqRows as $faqIndex => $faq)
                                <div class="landing-faq-row border rounded p-3 mb-3 bg-light" data-faq-row>
                                    <input
                                        type="hidden"
                                        name="landing_faqs[{{ $faqIndex }}][id]"
                                        value="{{ $faq['id'] ?? '' }}"
                                    >

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label>Pergunta</label>
                                                <input
                                                    type="text"
                                                    name="landing_faqs[{{ $faqIndex }}][question]"
                                                    class="form-control"
                                                    maxlength="255"
                                                    value="{{ $faq['question'] ?? '' }}"
                                                >
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Ordem</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    name="landing_faqs[{{ $faqIndex }}][sort_order]"
                                                    class="form-control"
                                                    value="{{ $faq['sort_order'] ?? ($faqIndex + 1) }}"
                                                >
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="d-block">Status</label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input
                                                    type="hidden"
                                                    name="landing_faqs[{{ $faqIndex }}][is_active]"
                                                    value="0"
                                                >
                                                <input
                                                    type="checkbox"
                                                    class="custom-control-input"
                                                    name="landing_faqs[{{ $faqIndex }}][is_active]"
                                                    id="landing_faq_active_{{ $faqIndex }}"
                                                    value="1"
                                                    @checked((bool) ($faq['is_active'] ?? true))
                                                >
                                                <label
                                                    class="custom-control-label"
                                                    for="landing_faq_active_{{ $faqIndex }}"
                                                >
                                                    Ativo
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Resposta</label>
                                                <textarea
                                                    name="landing_faqs[{{ $faqIndex }}][answer]"
                                                    class="form-control"
                                                    rows="3"
                                                    maxlength="5000"
                                                >{{ $faq['answer'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-12 text-right">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger js-remove-landing-faq"
                                            >
                                                <i class="fas fa-trash-alt mr-1"></i>
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

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Pergunta</label>
                                            <input
                                                type="text"
                                                data-name="question"
                                                class="form-control"
                                                maxlength="255"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Ordem</label>
                                            <input
                                                type="number"
                                                min="0"
                                                data-name="sort_order"
                                                class="form-control"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="d-block">Status</label>
                                        <div class="custom-control custom-switch mt-2">
                                            <input type="hidden" data-name="is_active_hidden" value="0">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input"
                                                data-name="is_active"
                                                value="1"
                                                checked
                                            >
                                            <label class="custom-control-label">Ativo</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Resposta</label>
                                            <textarea
                                                data-name="answer"
                                                class="form-control"
                                                rows="3"
                                                maxlength="5000"
                                            ></textarea>
                                        </div>
                                    </div>

                                    <div class="col-12 text-right">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger js-remove-landing-faq"
                                        >
                                            <i class="fas fa-trash-alt mr-1"></i>
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- 6. SEO --}}
            <div class="card card-outline card-secondary mb-0">
                <div class="card-header p-0" id="landing-heading-seo">
                    <button
                        type="button"
                        class="btn btn-block text-left landing-admin-collapse-btn collapsed"
                        data-toggle="collapse"
                        data-target="#landing-section-seo"
                        aria-expanded="false"
                        aria-controls="landing-section-seo"
                    >
                        <span>6. SEO</span>
                        <i class="fas fa-chevron-down landing-collapse-icon"></i>
                    </button>
                </div>

                <div
                    id="landing-section-seo"
                    class="collapse"
                    aria-labelledby="landing-heading-seo"
                    data-parent="#landing-config-accordion"
                >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-md-0">
                                    <label for="landing_seo_title">
                                        Título para Google
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <input
                                        type="text"
                                        name="landing_seo_title"
                                        id="landing_seo_title"
                                        class="form-control"
                                        maxlength="70"
                                        value="{{ old('landing_seo_title', $course->landing_seo_title ?? '') }}"
                                    >
                                    <small class="form-text text-muted">
                                        Máximo recomendado: 70 caracteres.
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="landing_seo_description">
                                        Descrição para Google
                                        <small class="text-muted font-weight-normal">(opcional)</small>
                                    </label>
                                    <textarea
                                        name="landing_seo_description"
                                        id="landing_seo_description"
                                        class="form-control"
                                        rows="2"
                                        maxlength="170"
                                    >{{ old('landing_seo_description', $course->landing_seo_description ?? '') }}</textarea>
                                    <small class="form-text text-muted">
                                        Máximo recomendado: 170 caracteres.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #landing-page-config .landing-admin-summary {
        height: 100%;
        padding: 14px 16px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #f8f9fa;
    }

    #landing-page-config .landing-admin-label {
        display: block;
        margin-bottom: 4px;
        color: #6c757d;
        font-size: 12px;
    }

    #landing-page-config .landing-admin-collapse-btn {
        display: flex;
        width: 100%;
        padding: 14px 16px;
        justify-content: space-between;
        align-items: center;
        border: 0;
        border-radius: 0;
        background: #fff;
        color: #0b1f3a;
        font-size: 17px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: none;
    }

    #landing-page-config .landing-admin-collapse-btn:hover,
    #landing-page-config .landing-admin-collapse-btn:focus {
        background: #f4f7fa;
        color: #0b1f3a;
        text-decoration: none;
        box-shadow: none;
    }

    #landing-page-config .landing-admin-collapse-btn:not(.collapsed) {
        background: #eef6fb;
        color: #0b1f3a;
    }

    #landing-page-config .landing-admin-feature {
        padding: 14px 16px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #f8f9fa;
    }

    #landing-page-config .landing-admin-preview-image {
        width: 100%;
        max-width: 420px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    #landing-page-config .landing-faq-row {
        border-color: #dee2e6 !important;
    }

    #landing-page-config .card-outline.card-secondary {
        border-top: 1px solid #ced4da;
    }

    @media (max-width: 767.98px) {
        #landing-page-config .landing-admin-collapse-btn {
            padding: 12px 14px;
            font-size: 15px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var faqList = document.getElementById('landing-faq-list');
    var addFaqButton = document.getElementById('add-landing-faq');
    var faqTemplate = document.getElementById('landing-faq-template');

    function bindRemoveButtons(scope) {
        if (!scope) return;

        scope.querySelectorAll('.js-remove-landing-faq').forEach(function (button) {
            if (button.dataset.bound === '1') return;

            button.dataset.bound = '1';

            button.addEventListener('click', function () {
                var row = button.closest('[data-faq-row]');
                if (row) row.remove();
            });
        });
    }

    function nextFaqIndex() {
        if (!faqList) return 0;

        var indexes = Array.from(
            faqList.querySelectorAll('[name^="landing_faqs["]')
        ).map(function (field) {
            var match = field.name.match(/^landing_faqs\[(\d+)\]/);
            return match ? Number(match[1]) : -1;
        });

        return indexes.length
            ? Math.max.apply(null, indexes) + 1
            : 0;
    }

    if (faqList && addFaqButton && faqTemplate) {
        bindRemoveButtons(faqList);

        addFaqButton.addEventListener('click', function () {
            var index = nextFaqIndex();
            var fragment = faqTemplate.content.cloneNode(true);
            var row = fragment.querySelector('[data-faq-row]');

            row.querySelector('[data-name="id"]').name =
                'landing_faqs[' + index + '][id]';

            row.querySelector('[data-name="question"]').name =
                'landing_faqs[' + index + '][question]';

            row.querySelector('[data-name="answer"]').name =
                'landing_faqs[' + index + '][answer]';

            row.querySelector('[data-name="sort_order"]').name =
                'landing_faqs[' + index + '][sort_order]';

            row.querySelector('[data-name="sort_order"]').value =
                index + 1;

            row.querySelector('[data-name="is_active_hidden"]').name =
                'landing_faqs[' + index + '][is_active]';

            var active = row.querySelector('[data-name="is_active"]');

            active.name =
                'landing_faqs[' + index + '][is_active]';

            active.id =
                'landing_faq_active_' + index;

            var activeLabel = active.nextElementSibling;

            if (activeLabel) {
                activeLabel.setAttribute('for', active.id);
            }

            faqList.appendChild(fragment);
            bindRemoveButtons(faqList);
        });
    }

    var performanceToggle =
        document.getElementById('landing_show_performance');

    var performanceImageGroup =
        document.getElementById('landing-performance-image-group');

    function syncPerformanceImageVisibility() {
        if (!performanceToggle || !performanceImageGroup) return;

        performanceImageGroup.style.display =
            performanceToggle.checked ? '' : 'none';
    }

    if (performanceToggle) {
        performanceToggle.addEventListener(
            'change',
            syncPerformanceImageVisibility
        );
    }

    syncPerformanceImageVisibility();

    $('#landing-config-accordion .collapse')
        .on('show.bs.collapse', function () {
            var button = $('[data-target="#' + this.id + '"]');
            var icon = button.find('.landing-collapse-icon');

            icon
                .removeClass('fa-chevron-down')
                .addClass('fa-chevron-up');
        })
        .on('hide.bs.collapse', function () {
            var button = $('[data-target="#' + this.id + '"]');
            var icon = button.find('.landing-collapse-icon');

            icon
                .removeClass('fa-chevron-up')
                .addClass('fa-chevron-down');
        });
});
</script>
@endpush
