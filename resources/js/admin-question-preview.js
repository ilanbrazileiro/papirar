document.addEventListener('DOMContentLoaded', () => {
    const previewButton = document.getElementById('papirar-question-preview-button');
    const previewModal = document.getElementById('papirar-question-preview-modal');
    const previewCloseButtons = document.querySelectorAll('[data-papirar-preview-close]');

    if (!previewButton || !previewModal) {
        return;
    }

    const getTinyContent = (selector) => {
        const textarea = document.querySelector(selector);
        if (!textarea) return '';

        if (window.tinymce) {
            const editor = window.tinymce.get(textarea.id);
            if (editor) {
                return editor.getContent();
            }
        }

        return textarea.value || '';
    };

    const getInputValue = (selector) => {
        const field = document.querySelector(selector);
        return field ? field.value : '';
    };

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    };

    const openModal = () => {
        const statement = getTinyContent('[name="statement"]');
        const commentedAnswer = getTinyContent('[name="commented_answer"]');
        const correctLetter = (getInputValue('[name="correct_letter"]') || '').toUpperCase();

        const alternatives = ['a', 'b', 'c', 'd', 'e'].map(letter => {
            const value = getInputValue(`[name="alternative_${letter}"]`);
            const upper = letter.toUpperCase();
            const isCorrect = correctLetter === upper;
            return `
                <div class="papirar-preview-alternative ${isCorrect ? 'is-correct' : ''}">
                    <span class="papirar-preview-letter">${upper}</span>
                    <div class="papirar-preview-alternative-text">${escapeHtml(value || 'Alternativa não preenchida')}</div>
                </div>
            `;
        }).join('');

        previewModal.querySelector('[data-preview-statement]').innerHTML = statement || '<p><em>Enunciado não preenchido.</em></p>';
        previewModal.querySelector('[data-preview-alternatives]').innerHTML = alternatives;
        previewModal.querySelector('[data-preview-commented-answer]').innerHTML = commentedAnswer || '<p><em>Comentário ainda não preenchido.</em></p>';
        previewModal.querySelector('[data-preview-correct-letter]').textContent = correctLetter || '-';

        previewModal.classList.add('is-open');
        document.body.classList.add('papirar-preview-open');
    };

    const closeModal = () => {
        previewModal.classList.remove('is-open');
        document.body.classList.remove('papirar-preview-open');
    };

    previewButton.addEventListener('click', openModal);

    previewCloseButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    previewModal.addEventListener('click', (event) => {
        if (event.target === previewModal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && previewModal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
