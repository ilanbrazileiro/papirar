@if($errors->any())
    <div class="alert alert-danger">
        <strong>Confira os campos abaixo:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="alert alert-info d-flex align-items-start mb-4">
    <div class="mr-3 me-3"><i class="fas fa-info-circle"></i></div>
    <div>
        <strong>Campos do card comercial:</strong> a capa do curso, descrição curta, chamada, selo e benefícios ficam no bloco
        <strong>Card comercial do curso</strong>, logo abaixo dos dados básicos. Esses dados aparecem em <code>/aluno/cursos</code>,
        <code>/aluno/assinaturas</code> e na dashboard do aluno sem curso ativo.
    </div>
</div>

<form method="POST" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}" enctype="multipart/form-data">
    @csrf
    @if($course->exists)
        @method('PUT')
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Dados comerciais</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Nome do curso</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $course->title ?? '') }}" required maxlength="180" placeholder="Ex.: CHOAE CBMERJ 2026">
                </div>

                <div class="col-md-6">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $course->slug ?? '') }}" maxlength="200" placeholder="Deixe em branco para gerar automaticamente">
                </div>

                <div class="col-md-3">
                    <label for="course_type" class="form-label">Tipo</label>
                    <select name="course_type" id="course_type" class="form-control" required>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('course_type', $course->course_type ?? 'internal_exam') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="price" class="form-label">Preço mensal</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" value="{{ old('price', $course->price ?? 0) }}" required>
                    <div class="text-muted small mt-1">Valor cobrado na assinatura mensal.</div>
                </div>

                <div class="col-md-3">
                    <label for="quarterly_price" class="form-label">Preço trimestral</label>
                    <input type="number" step="0.01" min="0" name="quarterly_price" id="quarterly_price" class="form-control" value="{{ old('quarterly_price', $course->quarterly_price ?? '') }}" placeholder="Opcional">
                    <div class="text-muted small mt-1">Preço total de 3 meses. Deixe vazio para não vender trimestral.</div>
                </div>

                <div class="col-md-3">
                    <label for="semiannual_price" class="form-label">Preço semestral</label>
                    <input type="number" step="0.01" min="0" name="semiannual_price" id="semiannual_price" class="form-control" value="{{ old('semiannual_price', $course->semiannual_price ?? '') }}" placeholder="Opcional">
                    <div class="text-muted small mt-1">Preço total de 6 meses. Deixe vazio para não vender semestral.</div>
                </div>

                <div class="col-md-3">
                    <label for="sort_order" class="form-label">Ordem</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $course->sort_order ?? 0) }}" min="0">
                </div>

                <div class="col-md-6">
                    <label for="corporation_id" class="form-label">Corporação vinculada</label>
                    <select name="corporation_id" id="corporation_id" class="form-control">
                        <option value="">Sem corporação específica</option>
                        @foreach($corporations as $corporation)
                            <option value="{{ $corporation->id }}" @selected((int) old('corporation_id', $course->corporation_id ?? 0) === (int) $corporation->id)>
                                {{ $corporation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="exam_id" class="form-label">Concurso vinculado</label>
                    <select name="exam_id" id="exam_id" class="form-control">
                        <option value="">Sem concurso específico</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" @selected((int) old('exam_id', $course->exam_id ?? 0) === (int) $exam->id)>
                                {{ $exam->title }} — {{ $exam->corporation->name ?? '-' }} {{ $exam->year ? '(' . $exam->year . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-muted small mt-1">Quando houver concurso vinculado, o curso pode herdar disciplinas, tópicos e fontes já cadastrados no concurso.</div>
                </div>


                <div class="col-12">
                    <label for="description" class="form-label">Descrição completa</label>
                    <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $course->description ?? '') }}</textarea>
                </div>

                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" @checked((bool) old('active', $course->active ?? true))>
                        <label class="form-check-label" for="active">Curso ativo</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_public" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_public" name="is_public" value="1" @checked((bool) old('is_public', $course->is_public ?? true))>
                        <label class="form-check-label" for="is_public">Exibir para venda</label>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_trial_available" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_trial_available" name="is_trial_available" value="1" @checked((bool) old('is_trial_available', $course->is_trial_available ?? true))>
                        <label class="form-check-label" for="is_trial_available">Liberar teste grátis</label>
                    </div>
                    <div class="text-muted small">Se marcado, o curso aparecerá no cadastro para teste gratuito.</div>
                </div>

                <div class="col-md-3">
                    <label for="trial_days" class="form-label">Dias grátis</label>
                    <input type="number" name="trial_days" id="trial_days" class="form-control" value="{{ old('trial_days', $course->trial_days ?? 7) }}" min="1" max="30">
                    <div class="text-muted small">Recomendado: 7 dias.</div>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="inherit_exam_scope" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="inherit_exam_scope" name="inherit_exam_scope" value="1" @checked((bool) old('inherit_exam_scope', $course->inherit_exam_scope ?? true))>
                        <label class="form-check-label" for="inherit_exam_scope">Herdar escopo do concurso vinculado</label>
                    </div>
                    <div class="text-muted small">Se marcado e houver concurso vinculado, a área do aluno poderá usar disciplinas/tópicos/fontes do concurso.</div>
                </div>
            </div>
        </div>
    </div>

    <div id="card-comercial-curso"></div>
    @include('admin.courses._marketing_block')

    <div class="card mb-4">
        <div class="card-header"><strong>Landing page de campanha</strong></div>
        <div class="card-body">
            <div class="alert alert-light border">
                A landing só ficará disponível em <code>/preparatorio/{{ $course->slug ?: 'slug-do-curso' }}</code>
                quando o curso estiver ativo, público e com a opção abaixo marcada. Números, disciplinas,
                tópicos, preços e período de teste são carregados automaticamente.
            </div>

            <div class="form-check form-switch mb-3">
                <input type="hidden" name="landing_enabled" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="landing_enabled" name="landing_enabled" value="1" @checked((bool) old('landing_enabled', $course->landing_enabled ?? false))>
                <label class="form-check-label" for="landing_enabled"><strong>Publicar landing de campanha</strong></label>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="landing_headline" class="form-label">Título principal</label>
                    <input type="text" name="landing_headline" id="landing_headline" class="form-control" maxlength="180" value="{{ old('landing_headline', $course->landing_headline ?? '') }}" placeholder="Prepare-se para Soldado PMERJ resolvendo questões">
                </div>
                <div class="col-md-6">
                    <label for="landing_cta_text" class="form-label">Texto dos botões</label>
                    <input type="text" name="landing_cta_text" id="landing_cta_text" class="form-control" maxlength="80" value="{{ old('landing_cta_text', $course->landing_cta_text ?? '') }}" placeholder="Começar teste grátis">
                </div>
                <div class="col-12">
                    <label for="landing_subheadline" class="form-label">Subtítulo</label>
                    <textarea name="landing_subheadline" id="landing_subheadline" class="form-control" rows="2" maxlength="500" placeholder="Explique como o Papirar ajuda este candidato.">{{ old('landing_subheadline', $course->landing_subheadline ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="landing_problem_title" class="form-label">Título da dor/problema</label>
                    <input type="text" name="landing_problem_title" id="landing_problem_title" class="form-control" maxlength="180" value="{{ old('landing_problem_title', $course->landing_problem_title ?? '') }}" placeholder="Estudar muito não basta se você não sabe onde está errando">
                </div>
                <div class="col-md-6">
                    <label for="landing_question_id" class="form-label">ID da questão demonstrativa</label>
                    <input type="number" min="1" name="landing_question_id" id="landing_question_id" class="form-control" value="{{ old('landing_question_id', $course->landing_question_id ?? '') }}" placeholder="Ex.: 1004">
                    <div class="text-muted small">A questão precisa estar publicada e pertencer ao escopo do curso.</div>
                </div>
                <div class="col-12">
                    <label for="landing_problem_text" class="form-label">Texto da dor/problema</label>
                    <textarea name="landing_problem_text" id="landing_problem_text" class="form-control" rows="3" maxlength="1000">{{ old('landing_problem_text', $course->landing_problem_text ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="landing_final_title" class="form-label">Título da chamada final</label>
                    <input type="text" name="landing_final_title" id="landing_final_title" class="form-control" maxlength="180" value="{{ old('landing_final_title', $course->landing_final_title ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="landing_final_text" class="form-label">Texto da chamada final</label>
                    <textarea name="landing_final_text" id="landing_final_text" class="form-control" rows="2" maxlength="500">{{ old('landing_final_text', $course->landing_final_text ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="landing_seo_title" class="form-label">Título para Google</label>
                    <input type="text" name="landing_seo_title" id="landing_seo_title" class="form-control" maxlength="70" value="{{ old('landing_seo_title', $course->landing_seo_title ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="landing_seo_description" class="form-label">Descrição para Google</label>
                    <textarea name="landing_seo_description" id="landing_seo_description" class="form-control" rows="2" maxlength="170">{{ old('landing_seo_description', $course->landing_seo_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Escopo manual do curso</strong>
                <div class="text-muted small">Use quando o curso não estiver vinculado a um concurso ou quando quiser um recorte próprio.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-all-course-subjects">Expandir/recolher</button>
        </div>

        <div class="card-body p-0">
            <div id="course-subjects-wrapper">
                @foreach($subjects as $subject)
                    @php
                        $subjectId = (int) $subject->id;
                        $oldSubjectSelected = old("subjects.$subjectId.selected");
                        $isSubjectSelected = $oldSubjectSelected !== null
                            ? (bool) $oldSubjectSelected
                            : in_array($subjectId, $selectedSubjects ?? [], true);
                        $oldTopics = old("subjects.$subjectId.topics");
                        $selectedTopicIds = $oldTopics !== null
                            ? array_map('intval', (array) $oldTopics)
                            : array_map('intval', $selectedTopicsBySubject[$subjectId] ?? []);
                        $topics = $topicsBySubject->get($subjectId, collect());
                        $topicsPanelId = 'course-subject-topics-' . $subjectId;
                    @endphp

                    <div class="border-bottom course-subject-item">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <div class="form-check mb-0">
                                <input type="hidden" name="subjects[{{ $subjectId }}][selected]" value="0">
                                <input class="form-check-input course-subject-checkbox" type="checkbox" id="course-subject-{{ $subjectId }}" name="subjects[{{ $subjectId }}][selected]" value="1" data-subject-id="{{ $subjectId }}" @checked($isSubjectSelected)>
                                <label class="form-check-label font-weight-bold" for="course-subject-{{ $subjectId }}">
                                    {{ $subject->name }}
                                    <span class="badge badge-light bg-light text-dark ml-2 ms-2">{{ $topics->count() }} tópico(s)</span>
                                </label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary toggle-course-topics" data-target="{{ $topicsPanelId }}">Tópicos</button>
                        </div>

                        <div id="{{ $topicsPanelId }}" class="course-topics-panel {{ $isSubjectSelected ? '' : 'd-none' }} p-3 border-top bg-light" data-subject-id="{{ $subjectId }}">
                            @if($topics->isEmpty())
                                <div class="alert alert-warning mb-0">Esta disciplina ainda não possui tópicos cadastrados.</div>
                            @else
                                <div class="d-flex mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-2 me-2 select-all-course-topics" data-subject-id="{{ $subjectId }}">Marcar todos</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary clear-all-course-topics" data-subject-id="{{ $subjectId }}">Limpar</button>
                                </div>

                                <div class="row course-topic-group" data-subject-id="{{ $subjectId }}">
                                    @foreach($topics as $topic)
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="form-check border rounded p-2 h-100 bg-white">
                                                <input class="form-check-input course-topic-checkbox course-topic-checkbox-{{ $subjectId }}" type="checkbox" id="course-topic-{{ $topic->id }}" name="subjects[{{ $subjectId }}][topics][]" value="{{ $topic->id }}" @checked(in_array((int) $topic->id, $selectedTopicIds, true))>
                                                <label class="form-check-label" for="course-topic-{{ $topic->id }}">{{ $topic->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Fontes/Bibliografias do curso</strong>
            <div class="text-muted small">Opcional. Use para permitir filtro por bibliografia dentro do curso.</div>
        </div>
        <div class="card-body">
            @if($sourceMaterials->isEmpty())
                <div class="alert alert-warning mb-0">Nenhuma fonte/bibliografia ativa cadastrada.</div>
            @else
                <div class="row g-2">
                    @foreach($sourceMaterials as $material)
                        @php
                            $materialId = (int) $material->id;
                            $oldMaterials = old('source_materials');
                            $isSelected = $oldMaterials !== null
                                ? in_array($materialId, array_map('intval', (array) $oldMaterials), true)
                                : in_array($materialId, $selectedSourceMaterials ?? [], true);
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check border rounded p-2 h-100">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="source-material-{{ $materialId }}" name="source_materials[]" value="{{ $materialId }}" @checked($isSelected)>
                                <label class="form-check-label" for="source-material-{{ $materialId }}">
                                    {{ $material->title }}
                                    <div class="text-muted small">{{ $material->subject->name ?? '-' }}</div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4" id="bundle-card">
        <div class="card-header">
            <strong>Cursos incluídos no combo</strong>
            <div class="text-muted small">Use apenas quando o tipo do curso for Combo.</div>
        </div>
        <div class="card-body">
            @if($availableCourses->isEmpty())
                <div class="alert alert-warning mb-0">Nenhum outro curso ativo disponível para incluir no combo.</div>
            @else
                <div class="row g-2">
                    @foreach($availableCourses as $availableCourse)
                        @php
                            $availableCourseId = (int) $availableCourse->id;
                            $oldBundleCourses = old('bundle_courses');
                            $isSelected = $oldBundleCourses !== null
                                ? in_array($availableCourseId, array_map('intval', (array) $oldBundleCourses), true)
                                : in_array($availableCourseId, $selectedBundleCourses ?? [], true);
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check border rounded p-2 h-100">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="bundle-course-{{ $availableCourseId }}" name="bundle_courses[]" value="{{ $availableCourseId }}" @checked($isSelected)>
                                <label class="form-check-label" for="bundle-course-{{ $availableCourseId }}">
                                    {{ $availableCourse->title }}
                                    <div class="text-muted small">{{ $availableCourse->typeLabel() }}</div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Voltar</a>
        <button type="submit" class="btn btn-primary">Salvar curso</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseType = document.getElementById('course_type');
    const bundleCard = document.getElementById('bundle-card');

    function toggleBundleCard() {
        if (!courseType || !bundleCard) return;
        bundleCard.style.display = courseType.value === 'combo' ? '' : 'none';
    }

    if (courseType) {
        courseType.addEventListener('change', toggleBundleCard);
        toggleBundleCard();
    }

    document.querySelectorAll('.toggle-course-topics').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const panel = document.getElementById(targetId);
            if (!panel) return;
            panel.classList.toggle('d-none');
        });
    });

    document.querySelectorAll('.course-subject-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const subjectId = this.dataset.subjectId;
            const panel = document.getElementById('course-subject-topics-' + subjectId);
            if (this.checked && panel) panel.classList.remove('d-none');
        });
    });

    document.querySelectorAll('.course-topic-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const group = this.closest('.course-topic-group');
            if (!group) return;
            const subjectId = group.dataset.subjectId;
            const subjectCheckbox = document.getElementById('course-subject-' + subjectId);
            if (this.checked && subjectCheckbox) {
                subjectCheckbox.checked = true;
                const panel = document.getElementById('course-subject-topics-' + subjectId);
                if (panel) panel.classList.remove('d-none');
            }
        });
    });

    document.querySelectorAll('.select-all-course-topics').forEach(function (button) {
        button.addEventListener('click', function () {
            const subjectId = this.dataset.subjectId;
            const subjectCheckbox = document.getElementById('course-subject-' + subjectId);
            if (subjectCheckbox) subjectCheckbox.checked = true;
            const panel = document.getElementById('course-subject-topics-' + subjectId);
            if (panel) panel.classList.remove('d-none');
            document.querySelectorAll('.course-topic-checkbox-' + subjectId).forEach(function (checkbox) {
                checkbox.checked = true;
            });
        });
    });

    document.querySelectorAll('.clear-all-course-topics').forEach(function (button) {
        button.addEventListener('click', function () {
            const subjectId = this.dataset.subjectId;
            document.querySelectorAll('.course-topic-checkbox-' + subjectId).forEach(function (checkbox) {
                checkbox.checked = false;
            });
        });
    });

    const toggleAll = document.getElementById('toggle-all-course-subjects');
    if (toggleAll) {
        toggleAll.addEventListener('click', function () {
            const panels = Array.from(document.querySelectorAll('.course-topics-panel'));
            const hasHidden = panels.some(panel => panel.classList.contains('d-none'));
            panels.forEach(panel => hasHidden ? panel.classList.remove('d-none') : panel.classList.add('d-none'));
        });
    }
});
</script>
@endpush
