(function () {
    function gtagAvailable() {
        return typeof window.gtag === 'function';
    }

    function send(eventName, params) {
        if (!gtagAvailable()) return;
        window.gtag('event', eventName, params || {});
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a');
        if (!link) return;

        var href = link.getAttribute('href') || '';
        var text = (link.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 100);

        if (href.indexOf('/cadastro') !== -1) {
            send('cta_register_click', {
                link_text: text,
                page_location: window.location.href
            });
            return;
        }

        if (href.indexOf('/cursos/') !== -1) {
            send('course_click', {
                link_text: text,
                link_url: link.href,
                page_location: window.location.href
            });
            return;
        }

        if (href.indexOf('/questoes') !== -1) {
            send('question_content_click', {
                link_text: text,
                link_url: link.href,
                page_location: window.location.href
            });
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        var action = form.getAttribute('action') || '';

        if (action.indexOf('/responder') !== -1) {
            send('question_answer_submit', {
                page_location: window.location.href
            });
        }
    });
})();
