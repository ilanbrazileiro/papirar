<script>
(function () {
    const button = document.getElementById('btn-check-question-duplicate');
    const result = document.getElementById('question-duplicate-result');
    const wrapper = document.getElementById('question-duplicate-checker');

    if (!button || !result || !wrapper) {
        return;
    }

    function getStatementValue() {
        if (window.tinymce) {
            const editor = tinymce.get('statement');
            if (editor) {
                return editor.getContent();
            }
        }

        const textarea = document.querySelector('[name="statement"]');
        return textarea ? textarea.value : '';
    }

    function renderMessage(html, type = 'info') {
        result.innerHTML = '<div class="alert alert-' + type + ' mb-0">' + html + '</div>';
    }

    button.addEventListener('click', async function () {
        const statement = getStatementValue();
        const questionId = wrapper.dataset.questionId || '';

        if (!statement || statement.replace(/<[^>]*>/g, '').trim().length < 10) {
            renderMessage('Digite ou cole o enunciado antes de verificar duplicidade.', 'warning');
            return;
        }

        button.disabled = true;
        button.innerText = 'Verificando...';
        renderMessage('Consultando questões existentes...', 'secondary');

        try {
            const response = await fetch('{{ route('admin.questions.check-duplicate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    statement: statement,
                    question_id: questionId || null
                })
            });

            if (!response.ok) {
                throw new Error('Falha na verificação.');
            }

            const data = await response.json();

            if (!data.has_duplicates) {
                renderMessage('Nenhuma questão idêntica encontrada.', 'success');
                return;
            }

            let html = '<strong>Possível duplicidade encontrada.</strong><br>';
            html += '<div class="table-responsive mt-2"><table class="table table-sm table-bordered mb-0">';
            html += '<thead><tr><th>ID</th><th>Status</th><th>Disciplina</th><th>Tópico</th><th>Prévia</th><th>Ações</th></tr></thead><tbody>';

            data.duplicates.forEach(function (item) {
                html += '<tr>';
                html += '<td>#' + item.id + '</td>';
                html += '<td>' + (item.status || '-') + '</td>';
                html += '<td>' + (item.subject || '-') + '</td>';
                html += '<td>' + (item.topic || '-') + '</td>';
                html += '<td>' + (item.statement_preview || '-') + '</td>';
                html += '<td><a class="btn btn-xs btn-outline-primary" href="' + item.show_url + '" target="_blank">Ver</a> ';
                html += '<a class="btn btn-xs btn-outline-secondary" href="' + item.edit_url + '" target="_blank">Editar</a></td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            renderMessage(html, 'danger');
        } catch (error) {
            renderMessage('Não foi possível verificar agora. Tente novamente.', 'danger');
        } finally {
            button.disabled = false;
            button.innerText = 'Verificar duplicidade';
        }
    });
})();
</script>
