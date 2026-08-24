(function () {
    function gtagAvailable() {
        return typeof window.gtag === 'function';
    }

    function send(eventName, params) {
        if (!gtagAvailable()) return;

        window.gtag('event', eventName, Object.assign({
            page_location: window.location.href
        }, params || {}));
    }

    function pathIncludes(value) {
        return window.location.pathname.indexOf(value) !== -1;
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        var action = form.getAttribute('action') || '';
        var method = (form.getAttribute('method') || 'GET').toUpperCase();

        if (method === 'POST' && action.indexOf('/checkout') !== -1) {
            var billingCycle = form.querySelector('[name="billing_cycle"]');

            send('begin_checkout', {
                billing_cycle: billingCycle ? billingCycle.value : null
            });

            return;
        }

        if (
            method === 'POST' &&
            (
                action.indexOf('/responder') !== -1 ||
                action.indexOf('/answer') !== -1
            )
        ) {
            send('answer_question');
            return;
        }

        if (
            method === 'POST' &&
            (
                action.indexOf('/simulados') !== -1 ||
                action.indexOf('/simulated') !== -1
            )
        ) {
            send('simulated_exam_action');
        }
    });

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a');

        if (!link) return;

        var href = link.getAttribute('href') || '';
        var text = (link.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 100);

        if (href.indexOf('/estudar') !== -1 || href.indexOf('/study') !== -1) {
            send('study_click', {
                link_text: text
            });
            return;
        }

        if (href.indexOf('/simulados') !== -1 || href.indexOf('/simulated') !== -1) {
            send('simulated_exam_click', {
                link_text: text
            });
        }
    });

    // Eventos de contexto de página sem dados pessoais.
    if (pathIncludes('/cursos/') && (pathIncludes('/estudar') || pathIncludes('/study'))) {
        send('study_page_view');
    }

    if (pathIncludes('/simulados') || pathIncludes('/simulated')) {
        send('simulated_exam_page_view');
    }
})();
