<script>
(function () {
    function initQuestionFormCorrections() {
        const form = document.getElementById('question-form');
        const subjectSelect = document.getElementById('subject_id');
        const topicSelect = document.getElementById('topic_id');

        if (!form || !subjectSelect || !topicSelect) {
            return;
        }

        const topicsUrl = @json(route('admin.questions.ajax.topics'));
        let submitting = false;
        let requestController = null;

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

        function topicHelp(message) {
            let help = document.getElementById('topic-help-dynamic');

            if (!help) {
                help = document.createElement('div');
                help.id = 'topic-help-dynamic';
                help.className = 'form-text';
                topicSelect.insertAdjacentElement('afterend', help);
            }

            help.textContent = message;
        }

        function replaceTopicOptions(results, selectedId) {
            if (window.jQuery && $.fn.select2 && $('#topic_id').hasClass('select2-hidden-accessible')) {
                $('#topic_id').empty().append(new Option('Selecione um assunto', '', false, false));

                results.forEach(function (topic) {
                    const selected = selectedId !== '' && String(topic.id) === String(selectedId);
                    $('#topic_id').append(new Option(topic.text, topic.id, selected, selected));
                });

                $('#topic_id').trigger('change.select2');
                return;
            }

            topicSelect.innerHTML = '';
            topicSelect.appendChild(new Option('Selecione um assunto', ''));

            results.forEach(function (topic) {
                const option = new Option(topic.text, topic.id);
                option.selected = selectedId !== '' && String(topic.id) === String(selectedId);
                topicSelect.appendChild(option);
            });
        }

        async function loadTopics(preserveCurrent) {
            const subjectId = subjectSelect.value;

            if (!subjectId) {
                replaceTopicOptions([], '');
                topicSelect.disabled = true;
                topicHelp('Selecione uma disciplina para carregar os assuntos.');
                return;
            }

            if (requestController) {
                requestController.abort();
            }

            requestController = new AbortController();
            const selectedId = preserveCurrent ? String(topicSelect.value || '') : '';

            topicSelect.disabled = true;
            topicHelp('Carregando assuntos...');

            try {
                const url = new URL(topicsUrl, window.location.origin);
                url.searchParams.set('subject_id', subjectId);
                url.searchParams.set('q', '');

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) {
                    throw new Error('Falha ao carregar assuntos.');
                }

                const payload = await response.json();
                const results = Array.isArray(payload.results) ? payload.results : [];

                replaceTopicOptions(results, selectedId);
                topicSelect.disabled = false;
                topicHelp(results.length
                    ? results.length + (results.length === 1 ? ' assunto disponível.' : ' assuntos disponíveis.')
                    : 'Nenhum assunto cadastrado para esta disciplina.');
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                replaceTopicOptions([], '');
                topicSelect.disabled = false;
                topicHelp('Não foi possível carregar os assuntos. Troque a disciplina e tente novamente.');
                console.error(error);
            }
        }

        subjectSelect.addEventListener('change', function () {
            loadTopics(false);
        });

        loadTopics(true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuestionFormCorrections);
    } else {
        initQuestionFormCorrections();
    }
})();
</script>
