@php
    $courseSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->title,
        'description' => $seoDescription,
        'url' => $canonicalUrl,
        'provider' => [
            '@type' => 'Organization',
            'name' => 'Papirar Concursos',
            'url' => route('site.home'),
        ],
    ];

    $faqSchema = null;

    if (
        isset($course->landingFaqs)
        && $course->landingFaqs->isNotEmpty()
    ) {
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $course->landingFaqs
                ->map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => trim((string) $faq->question),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => trim(strip_tags((string) $faq->answer)),
                        ],
                    ];
                })
                ->values()
                ->all(),
        ];
    }
@endphp

<script type="application/ld+json">{!! json_encode(
    $courseSchema,
    JSON_UNESCAPED_SLASHES
    | JSON_UNESCAPED_UNICODE
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
) !!}</script>

@if($faqSchema)
<script type="application/ld+json">{!! json_encode(
    $faqSchema,
    JSON_UNESCAPED_SLASHES
    | JSON_UNESCAPED_UNICODE
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
) !!}</script>
@endif
