@php
    $videoLesson = $question->activeVideoLesson ?? null;
@endphp

@if($videoLesson)
    @php
        $embedUrl = method_exists($videoLesson, 'resolvedEmbedUrl') ? $videoLesson->resolvedEmbedUrl() : ($videoLesson->embed_url ?? null);
    @endphp

    <div class="card-soft p-4 mt-4 border border-primary-subtle">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <div class="section-title mb-1">Aula relacionada à questão</div>
                <p class="small-muted mb-0">{{ $videoLesson->title ?: 'Assista à explicação desta questão.' }}</p>
            </div>
            @if(method_exists($videoLesson, 'formattedDuration') && $videoLesson->formattedDuration())
                <span class="badge text-bg-light align-self-start">Duração: {{ $videoLesson->formattedDuration() }}</span>
            @endif
        </div>

        @if($embedUrl)
            <div class="ratio ratio-16x9 rounded-4 overflow-hidden bg-dark">
                <iframe src="{{ $embedUrl }}" title="{{ $videoLesson->title ?: 'Aula da questão' }}" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
            </div>
        @elseif(!empty($videoLesson->video_url))
            <a href="{{ $videoLesson->video_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary">Abrir aula em vídeo</a>
        @else
            <div class="small-muted">Aula cadastrada, mas sem link de vídeo disponível.</div>
        @endif

        @if(!empty($videoLesson->notes))
            <div class="small-muted mt-3">{!! nl2br(e($videoLesson->notes)) !!}</div>
        @endif
    </div>
@endif
