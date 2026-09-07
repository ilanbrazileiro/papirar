(function () {
    var config = window.PapirarCourseLanding || {};

    function send(name, params) {
        if (typeof window.gtag !== 'function') return;
        window.gtag('event', name, Object.assign({}, config, params || {}));
    }

    send('landing_page_view');

    document.querySelectorAll('.js-landing-cta').forEach(function (link) {
        link.addEventListener('click', function () {
            send('landing_cta_click', {
                cta_position: link.dataset.position || 'unknown',
                link_url: link.href
            });
        });
    });
})();
