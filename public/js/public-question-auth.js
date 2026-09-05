(function () {
    const modal = document.getElementById('authModal');
    const backdrop = document.getElementById('authModalBackdrop');
    if (!modal || !backdrop) return;

    const close = document.getElementById('authModalClose');
    const config = window.PapirarPublicQuestionAuth || {};
    const questionForm = document.getElementById('publicQuestionForm');

    function setMode(mode) {
        document.querySelectorAll('[data-auth-tab]').forEach(el => el.classList.toggle('is-active', el.dataset.authTab === mode));
        document.querySelectorAll('[data-auth-panel]').forEach(el => el.hidden = el.dataset.authPanel !== mode);
        document.querySelectorAll('[data-form-errors]').forEach(el => { el.innerHTML = ''; el.classList.remove('is-visible'); });
    }

    function open(mode) {
        setMode(mode || 'register');
        modal.hidden = false;
        backdrop.hidden = false;
        document.body.classList.add('body-modal-open');
    }

    function hide() {
        modal.hidden = true;
        backdrop.hidden = true;
        document.body.classList.remove('body-modal-open');
    }

    async function send(form, url, mode) {
        const errors = document.querySelector(`[data-form-errors="${mode}"]`);
        errors.innerHTML = '';
        errors.classList.remove('is-visible');

        const payload = Object.fromEntries(new FormData(form).entries());
        const button = form.querySelector('button[type=submit]');
        const original = button.textContent;
        button.disabled = true;
        button.textContent = 'Aguarde...';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.authenticated) {
                const messages = [];
                if (data.errors) Object.values(data.errors).forEach(group => Array.isArray(group) && messages.push(...group));
                if (!messages.length) messages.push(data.message || 'Não foi possível concluir.');
                errors.textContent = messages.join(' ');
                errors.classList.add('is-visible');
                return;
            }

            if (typeof window.gtag === 'function') {
                window.gtag('event', 'sign_up', {
                    method: 'public_question_modal'
                });
            }

            window.location.href = config.continueUrl || window.location.href;
        } catch (e) {
            errors.textContent = 'Falha de comunicação. Tente novamente.';
            errors.classList.add('is-visible');
        } finally {
            button.disabled = false;
            button.textContent = original;
        }
    }

    document.querySelectorAll('.js-open-auth-modal').forEach(btn => btn.addEventListener('click', () => open(btn.dataset.mode)));
    document.querySelectorAll('[data-auth-tab]').forEach(btn => btn.addEventListener('click', () => setMode(btn.dataset.authTab)));
    close.addEventListener('click', hide);
    backdrop.addEventListener('click', hide);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) hide(); });

    document.getElementById('modalRegisterForm')?.addEventListener('submit', e => {
        e.preventDefault();
        send(e.currentTarget, config.registerUrl, 'register');
    });

    document.getElementById('modalLoginForm')?.addEventListener('submit', e => {
        e.preventDefault();
        send(e.currentTarget, config.loginUrl, 'login');
    });

    questionForm?.addEventListener('submit', e => {
        if (!config.gateReached && questionForm.dataset.guestLimitReached !== '1') return;

        e.preventDefault();

        if (!questionForm.querySelector('input[name="alternative_id"]:checked')) {
            questionForm.reportValidity();
            return;
        }

        open('register');
    });

    if (config.openOnLoad) open('register');
})();
