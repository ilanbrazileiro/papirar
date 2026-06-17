@php
    $lesson = $question->activeVideoLesson ?? null;
    $embedUrl = $lesson?->resolvedEmbedUrl();
@endphp

@if($lesson)
    <div class="card-soft p-4 mt-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
            <div>
                <div class="section-title mb-1">Aula em vídeo</div>
                <div class="small-muted">
                    {{ $lesson->title }}
                    @if($lesson->formattedDuration())
                        · {{ $lesson->formattedDuration() }}
                    @endif
                </div>
            </div>
            <span class="meta-badge align-self-start">{{ $lesson->providerLabel() }}</span>
        </div>

        @if($embedUrl)
            <div class="ratio ratio-16x9 mb-3">
                <iframe src="{{ $embedUrl }}" title="{{ $lesson->title }}" allowfullscreen loading="lazy"></iframe>
            </div>
        @elseif($lesson->video_url)
            <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary">Abrir aula em vídeo</a>
        @else
            <p class="small-muted mb-0">Aula cadastrada, mas sem URL de exibição.</p>
        @endif
    </div>
@endif
