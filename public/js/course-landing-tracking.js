(function () {
    'use strict';

    var config = window.PapirarCourseLanding || {};

    function send(name, params) {
        if (typeof window.gtag !== 'function') return;

        window.gtag(
            'event',
            name,
            Object.assign({}, config, params || {})
        );
    }

    function once(element, key) {
        if (!element) return false;

        var dataKey = 'papirarTracked' + key;

        if (element.dataset[dataKey] === '1') {
            return false;
        }

        element.dataset[dataKey] = '1';

        return true;
    }

    send('landing_page_view', {
        page_path: window.location.pathname
    });

    document
        .querySelectorAll('.js-landing-cta')
        .forEach(function (link) {
            link.addEventListener('click', function () {
                send('landing_cta_click', {
                    cta_position: link.dataset.position || 'unknown',
                    link_url: link.href
                });
            });
        });

    var demoForm = document.querySelector('.js-landing-demo-form');

    if (demoForm) {
        demoForm.addEventListener('change', function (event) {
            if (
                event.target
                && event.target.matches('input[name="alternative_id"]')
                && once(demoForm, 'DemoStart')
            ) {
                send('landing_demo_start', {
                    question_id:
                        document
                            .querySelector('.js-landing-demo')
                            ?.querySelector('.landing-question-meta span')
                            ?.textContent
                            ?.replace(/\D+/g, '') || null
                });
            }
        });

        demoForm.addEventListener('submit', function () {
            send('landing_demo_submit');
        });
    }

    document
        .querySelectorAll('.js-landing-faq')
        .forEach(function (item) {
            item.addEventListener('toggle', function () {
                if (!item.open) return;

                send('landing_faq_interaction', {
                    faq_id: item.dataset.faqId || null,
                    faq_question:
                        item.querySelector('summary')
                            ?.textContent
                            ?.trim()
                            ?.slice(0, 120) || null
                });
            });
        });

    function observeOnce(selector, eventName, paramsBuilder) {
        var element = document.querySelector(selector);

        if (!element) return;

        if (!('IntersectionObserver' in window)) {
            return;
        }

        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;

                    if (once(element, eventName)) {
                        send(
                            eventName,
                            typeof paramsBuilder === 'function'
                                ? paramsBuilder(element)
                                : {}
                        );
                    }

                    observer.unobserve(element);
                });
            },
            {
                threshold: 0.35
            }
        );

        observer.observe(element);
    }

    observeOnce(
        '.js-landing-demo',
        'landing_demo_view'
    );

    observeOnce(
        '.js-landing-offer',
        'landing_offer_view'
    );

    var sectionSelectors = [
        ['.landing-feature-performance', 'performance'],
        ['.landing-review-card', 'error_review'],
        ['.landing-direction-card', 'next_study'],
        ['.landing-organization-grid', 'organization'],
        ['.landing-simulations', 'simulations'],
        ['.landing-faq', 'faq']
    ];

    sectionSelectors.forEach(function (item) {
        var selector = item[0];
        var sectionName = item[1];

        observeOnce(
            selector,
            'landing_section_view',
            function () {
                return {
                    section_name: sectionName
                };
            }
        );
    });
})();
