<script>
(function () {
    function initSourceMaterialSelect() {
        const sourceMaterialSelect = document.getElementById('source_material_id');
        const corporationSelect = document.getElementById('corporation_id');
        const examSelect = document.getElementById('exam_id');
        const subjectSelect = document.getElementById('subject_id');

        if (!sourceMaterialSelect) {
            return;
        }

        const sourceMaterialsUrl = @json(route('admin.questions.ajax-source-materials'));
        let requestController = null;

        function help(message) {
            let helpElement = document.getElementById('source-material-help-dynamic');

            if (!helpElement) {
                helpElement = document.createElement('div');
                helpElement.id = 'source-material-help-dynamic';
                helpElement.className = 'form-text';
                sourceMaterialSelect.insertAdjacentElement('afterend', helpElement);
            }

            helpElement.textContent = message;
        }

        function replaceOptions(results, selectedId) {
            const isSelect2 = window.jQuery && $.fn.select2 && $('#source_material_id').hasClass('select2-hidden-accessible');

            if (isSelect2) {
                $('#source_material_id').empty().append(new Option('Sem fonte específica', '', false, false));

                results.forEach(function (material) {
                    const selected = selectedId !== '' && String(material.id) === String(selectedId);
                    $('#source_material_id').append(new Option(material.text, material.id, selected, selected));
                });

                $('#source_material_id').trigger('change.select2');
                return;
            }

            sourceMaterialSelect.innerHTML = '';
            sourceMaterialSelect.appendChild(new Option('Sem fonte específica', ''));

            results.forEach(function (material) {
                const option = new Option(material.text, material.id);
                option.selected = selectedId !== '' && String(material.id) === String(selectedId);
                sourceMaterialSelect.appendChild(option);
            });
        }

        async function loadSourceMaterials(preserveCurrent) {
            if (requestController) {
                requestController.abort();
            }

            requestController = new AbortController();
            const selectedId = preserveCurrent ? String(sourceMaterialSelect.value || '') : '';
            const corporationId = corporationSelect ? corporationSelect.value : '';
            const examId = examSelect ? examSelect.value : '';
            const subjectId = subjectSelect ? subjectSelect.value : '';

            sourceMaterialSelect.disabled = true;
            help('Carregando fontes/bibliografias...');

            try {
                const url = new URL(sourceMaterialsUrl, window.location.origin);
                url.searchParams.set('q', '');

                if (corporationId) {
                    url.searchParams.set('corporation_id', corporationId);
                }

                if (examId) {
                    url.searchParams.set('exam_id', examId);
                }

                if (subjectId) {
                    url.searchParams.set('subject_id', subjectId);
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) {
                    throw new Error('Falha ao carregar fontes.');
                }

                const payload = await response.json();
                const results = Array.isArray(payload.results) ? payload.results : [];

                replaceOptions(results, selectedId);
                sourceMaterialSelect.disabled = false;

                help(results.length
                    ? results.length + (results.length === 1 ? ' fonte disponível.' : ' fontes disponíveis.')
                    : 'Nenhuma fonte encontrada para os filtros atuais.');
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                replaceOptions([], '');
                sourceMaterialSelect.disabled = false;
                help('Erro ao carregar fontes/bibliografias. Ajuste os filtros e tente novamente.');
                console.error(error);
            }
        }

        if (window.jQuery && $.fn.select2) {
            $('#source_material_id').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                placeholder: $('#source_material_id').data('placeholder') || 'Selecione a fonte/material',
                ajax: {
                    url: sourceMaterialsUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            corporation_id: $('#corporation_id').val(),
                            exam_id: $('#exam_id').val(),
                            subject_id: $('#subject_id').val()
                        };
                    },
                    processResults: function (data) {
                        return data;
                    }
                }
            });
        } else {
            loadSourceMaterials(true);
        }

        ['corporation_id', 'exam_id', 'subject_id'].forEach(function (id) {
            const element = document.getElementById(id);

            if (!element) {
                return;
            }

            element.addEventListener('change', function () {
                if (window.jQuery && $.fn.select2 && $('#source_material_id').hasClass('select2-hidden-accessible')) {
                    $('#source_material_id').val(null).trigger('change');
                    return;
                }

                loadSourceMaterials(false);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSourceMaterialSelect);
    } else {
        initSourceMaterialSelect();
    }
})();
</script>
