@php
    $ga4MeasurementId = config('services.analytics.ga4_measurement_id');
    $ga4Event = session()->pull('ga4_event');
@endphp

@if($ga4MeasurementId)
    <script
        async
        src="https://www.googletagmanager.com/gtag/js?id={{ $ga4MeasurementId }}"
    ></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        window.gtag = gtag;

        gtag('js', new Date());

        gtag('config', @json($ga4MeasurementId), {
            anonymize_ip: true
        });

        @if(is_array($ga4Event) && !empty($ga4Event['name']))
            gtag(
                'event',
                @json($ga4Event['name']),
                @json($ga4Event['params'] ?? [])
            );
        @endif
    </script>
@endif
