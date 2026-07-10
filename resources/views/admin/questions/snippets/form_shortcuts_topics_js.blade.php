<script>
(function () {
    function initQuestionFormCorrections() {
        const form = document.getElementById('question-form');
        const corporationSelect = document.getElementById('corporation_id');
        const subjectSelect = document.getElementById('subject_id');
        const topicSelect = document.getElementById('topic_id');
        const examSelect = document.getElementById('exam_id');

        if (!form) {
            return;
        }

        const topicsUrl = @json(route('admin.questions.ajax.topics'));
        const examsUrl = @json(route('admin.questions.ajax.exams'));

        let submitting = false;
        let topicRequestController = null;
        let examRequestController = null;

        function syncEditors() {
            if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
                window.tinymce.triggerSave();
            }
        }

        function submitForm() {
            if (submitting) {
                return;
            }

            syncEditors();
            submitting = true;

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.innerHTML;
                submitButton.innerHTML = 'Salvando...';
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }

            window.setTimeout(function () {
                submitting = false;

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.dataset.originalText || 'Salvar questão';
                }
            }, 5000);
        }

        document.addEventListener('keydown', function (event) {
            if (!(event.ctrlKey || event.metaKey) || event.key.toLowerCase() !== 's') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            submitForm();
        }, true);

        form.addEventListener('submit', function () {
            syncEditors();
            submitting = true;
        });

        function helpAfter(select, id, message) {
            if (!select) {
                return;
            }

            let help = document.getElementById(id);

            if (!help) {
                help = document.createElement('div');
                help.id = id;
                help.className = 'form-text';
                select.insertAdjacentElement('afterend', help);
            }

            help.textContent = message;
        }

        function replaceOptions(select, placeholder, results, selectedId) {
            if (!select) {
                return;
            }

            const isSelect2 = window.jQuery && $.fn.select2 && $('#' + select.id).hasClass('select2-hidden-accessible');

            if (isSelect2) {
                const $select = $('#' + select.id);
                $select.empty().append(new Option(placeholder, '', false, false));

                results.forEach(function (item) {
                    const selected = selectedId !== '' && String(item.id) === String(selectedId);
                    $select.append(new Option(item.text, item.id, selected, selected));
                });

                $select.trigger('change.select2');
                return;
            }

            select.innerHTML = '';
            select.appendChild(new Option(placeholder, ''));

            results.forEach(function (item) {
                const option = new Option(item.text, item.id);
                option.selected = selectedId !== '' && String(item.id) === String(selectedId);
                select.appendChild(option);
            });
        }

        async function fetchResults(url, params, controller) {
            const requestUrl = new URL(url, window.location.origin);

            Object.keys(params).forEach(function (key) {
                if (params[key] !== null && params[key] !== undefined) {
                    requestUrl.searchParams.set(key, params[key]);
                }
            });

            const response = await fetch(requestUrl.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error('Falha ao carregar dados.');
            }

            const payload = await response.json();
            return Array.isArray(payload.results) ? payload.results : [];
        }

        async function loadTopics(preserveCurrent) {
            if (!subjectSelect || !topicSelect) {
                return;
            }

            const subjectId = subjectSelect.value;

            if (!subjectId) {
                replaceOptions(topicSelect, 'Selecione uma disciplina primeiro', [], '');
                topicSelect.disabled = true;
                helpAfter(topicSelect, 'topic-help-dynamic', 'Selecione uma disciplina para carregar os assuntos.');
                return;
            }

            if (topicRequestController) {
                topicRequestController.abort();
            }

            topicRequestController = new AbortController();
            const selectedId = preserveCurrent ? String(topicSelect.value || '') : '';

            topicSelect.disabled = true;
            helpAfter(topicSelect, 'topic-help-dynamic', 'Carregando assuntos...');

            try {
                const results = await fetchResults(topicsUrl, { subject_id: subjectId, q: '' }, topicRequestController);

                replaceOptions(topicSelect, 'Selecione um assunto', results, selectedId);
                topicSelect.disabled = false;

                helpAfter(topicSelect, 'topic-help-dynamic', results.length
                    ? results.length + (results.length === 1 ? ' assunto disponível.' : ' assuntos disponíveis.')
                    : 'Nenhum assunto cadastrado para esta disciplina.');
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                replaceOptions(topicSelect, 'Não foi possível carregar os assuntos', [], '');
                topicSelect.disabled = false;
                helpAfter(topicSelect, 'topic-help-dynamic', 'Erro ao carregar assuntos. Troque a disciplina e tente novamente.');
                console.error(error);
            }
        }

        async function loadExams(preserveCurrent) {
            if (!examSelect) {
                return;
            }

            if (window.jQuery && $.fn.select2 && $('#exam_id').hasClass('select2-hidden-accessible')) {
                return;
            }

            if (examRequestController) {
                examRequestController.abort();
            }

            examRequestController = new AbortController();
            const selectedId = preserveCurrent ? String(examSelect.value || '') : '';
            const corporationId = corporationSelect ? corporationSelect.value : '';

            examSelect.disabled = true;
            helpAfter(examSelect, 'exam-help-dynamic', 'Carregando concursos/provas...');

            try {
                const results = await fetchResults(examsUrl, { corporation_id: corporationId, q: '' }, examRequestController);

                replaceOptions(examSelect, 'Sem prova de origem', results, selectedId);
                examSelect.disabled = false;

                helpAfter(examSelect, 'exam-help-dynamic', results.length
                    ? results.length + (results.length === 1 ? ' concurso/prova disponível.' : ' concursos/provas disponíveis.')
                    : 'Nenhum concurso/prova encontrado para o filtro atual.');
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                replaceOptions(examSelect, 'Não foi possível carregar concursos/provas', [], '');
                examSelect.disabled = false;
                helpAfter(examSelect, 'exam-help-dynamic', 'Erro ao carregar concursos/provas. Troque a corporação e tente novamente.');
                console.error(error);
            }
        }

        if (subjectSelect) {
            subjectSelect.addEventListener('change', function () {
                loadTopics(false);
            });

            loadTopics(true);
        }

        if (corporationSelect) {
            corporationSelect.addEventListener('change', function () {
                loadExams(false);
            });
        }

        loadExams(true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuestionFormCorrections);
    } else {
        initQuestionFormCorrections();
    }
})();
</script>
