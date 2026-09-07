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

    var continueStudying = document.querySelector('[data-continue-studying]');

    document.querySelectorAll('[data-adaptive-recommendation]').forEach(function (card) {
        var params = { course_id: card.dataset.courseId || null, subject_id: card.dataset.subjectId || null, topic_id: card.dataset.topicId || null, current_accuracy: Number(card.dataset.accuracy || 0) };
        send('adaptive_recommendation_view', params);
        card.querySelector('[data-adaptive-start]')?.addEventListener('submit', function () { send('adaptive_recommendation_start', params); });
    });

    var adaptiveOutcome = document.querySelector('[data-adaptive-outcome]');
    if (adaptiveOutcome) {
        send('adaptive_recommendation_completed', {
            improved: adaptiveOutcome.dataset.improved === '1',
            previous_accuracy: Number(adaptiveOutcome.dataset.previousAccuracy || 0),
            current_accuracy: Number(adaptiveOutcome.dataset.currentAccuracy || 0)
        });
    }

    document.querySelectorAll('[data-trial-lifecycle]').forEach(function (card) {
        send('trial_lifecycle_view', {
            lifecycle_stage: card.dataset.stage || 'unknown',
            course_id: card.dataset.courseId || null,
            days_remaining: Number(card.dataset.daysRemaining || 0),
            questions_answered: Number(card.dataset.answers || 0)
        });

        card.querySelectorAll('[data-trial-action]').forEach(function (action) {
            var eventType = action.dataset.trialAction === 'checkout' ? 'trial_conversion_click' : 'trial_study_click';
            action.addEventListener(action.tagName === 'FORM' ? 'submit' : 'click', function () {
                send(eventType, {
                    lifecycle_stage: card.dataset.stage || 'unknown',
                    course_id: card.dataset.courseId || null,
                    days_remaining: Number(card.dataset.daysRemaining || 0),
                    billing_cycle: action.dataset.billingCycle || null
                });
            });
        });
    });

    var dailyMissions = document.querySelector('[data-daily-missions-collapse]');

    var studyPlan = document.querySelector('[data-study-plan-view]');
    if (studyPlan) {
        send('study_plan_today_view', { course_id: studyPlan.dataset.courseId || null });
    }

    document.querySelectorAll('[data-study-plan-start]').forEach(function (form) {
        form.addEventListener('submit', function () { send('study_plan_start', { course_id: studyPlan ? studyPlan.dataset.courseId : null }); });
    });

    var studyPlanForm = document.querySelector('[data-study-plan-form]');
    if (studyPlanForm) {
        studyPlanForm.addEventListener('submit', function () { send('study_plan_saved'); });
    }

    if (dailyMissions) {
        dailyMissions.addEventListener('shown.bs.collapse', function () {
            send('daily_missions_view');
        }, { once: true });
    }

    document.querySelectorAll('[data-daily-mission-completed]').forEach(function (mission) {
        send('daily_mission_completed', {
            mission_code: mission.dataset.dailyMissionCompleted
        });
    });

    if (continueStudying) {
        send('continue_studying_view', {
            continuation_type: continueStudying.dataset.continuationType || 'unknown',
            course_id: continueStudying.dataset.courseId || null
        });

        continueStudying.addEventListener('click', function () {
            send('continue_studying_click', {
                continuation_type: continueStudying.dataset.continuationType || 'unknown',
                course_id: continueStudying.dataset.courseId || null
            });
        });
    }

    document.querySelectorAll('[data-error-review-view]').forEach(function (element) {
        send('error_review_view', {
            course_id: element.dataset.courseId || null,
            pending_errors: Number(element.dataset.pendingErrors || 0)
        });
    });

    var completedReview = document.querySelector('[data-error-review-completed]');

    if (completedReview) {
        send('error_review_completed', {
            course_id: completedReview.dataset.courseId || null,
            reviewed: Number(completedReview.dataset.reviewed || 0),
            corrected: Number(completedReview.dataset.corrected || 0),
            still_pending: Number(completedReview.dataset.stillPending || 0)
        });
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        var action = form.getAttribute('action') || '';
        var method = (form.getAttribute('method') || 'GET').toUpperCase();

        if (method === 'POST' && (form.matches('[data-error-review-start]') || new FormData(form).get('mode') === 'review')) {
            send('error_review_start');
        }

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
