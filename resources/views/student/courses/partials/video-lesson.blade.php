@php
    $videoLesson = $question->activeVideoLesson ?? null;
@endphp

@if($videoLesson)
    @php
        $embedUrl = method_exists($videoLesson, 'resolvedEmbedUrl') ? $videoLesson->resolvedEmbedUrl() : ($videoLesson->embed_url ?? null);
    @endphp
    <div class="card-soft p-4 mt-4 border border-primary-subtle">
        <div class="section-title mb-1">Aula relacionada à questão</div>
        <p class="small-muted">{{ $videoLesson->title ?: 'Assista à explicação desta questão.' }}</p>
        @if($embedUrl)
            <div class="ratio ratio-16x9 rounded-4 overflow-hidden bg-dark">
                <iframe src="{{ $embedUrl }}" title="{{ $videoLesson->title ?: 'Aula da questão' }}" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
            </div>
        @elseif(!empty($videoLesson->video_url))
            <a href="{{ $videoLesson->video_url }}" class="btn btn-outline-primary" target="_blank" rel="noopener">Abrir aula em vídeo</a>
        @endif
        @if(!empty($videoLesson->notes))
            <div class="small-muted mt-3">{!! nl2br(e($videoLesson->notes)) !!}</div>
        @endif
    </div>
@endif

@php
    $relatedLessons = collect();
    $relatedCourse = $session->course;
    if ($relatedCourse && $question->subject_id && $question->topic_id) {
        $relatedScope = app(\App\Services\TopicVideos\TopicVideoScope::class)->forCourse($relatedCourse);
        if (in_array((int)$question->subject_id, $relatedScope['subjects']) && in_array((int)$question->topic_id, $relatedScope['topics'])) {
            $relatedLessons = \App\Models\TopicVideoLesson::query()
                ->where('subject_id', $question->subject_id)
                ->where('topic_id', $question->topic_id)
                ->where('status', 'published')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(6)->get();
        }
    }
@endphp

@if($relatedLessons->isNotEmpty())
    <section class="card-soft p-4 mt-4 pv-recommendation" aria-label="Videoaulas recomendadas">
        <span class="pv-eyebrow">▶ AULA RECOMENDADA</span>
        <div class="section-title mb-2 pv-recommendation-title">Aprofunde este assunto</div>
        <p class="small-muted mb-3">Videoaulas relacionadas a {{ $question->topic?->name }}. Assista e continue na questão.</p>
        @foreach($relatedLessons as $video)
            @php
                $canWatch = app(\App\Services\TopicVideos\TopicVideoAccess::class)->allowed($video, auth()->user(), $relatedCourse);
                $playerUrl = $canWatch ? $video->embedUrl() : null;
                $modalId = 'pv-review-video-' . $question->id . '-' . $video->id;
            @endphp
            <div class="pv-lesson-row">
                <div class="pv-lesson-info">
                    <strong class="pv-lesson-title">{{ $video->title }}</strong>
                    <span class="small-muted d-block mt-1">{{ $video->teacher }} · {{ $video->origin === 'youtube' ? 'YouTube' : 'Papirar exclusivo' }}</span>
                </div>
                @if($playerUrl)
                    <button type="button" class="btn pv-btn-yellow" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">▶ Assistir aula</button>
                @elseif(!$canWatch)
                    <span class="pv-locked">Exclusiva para assinantes</span>
                @else
                    <span class="small-muted">Player indisponível</span>
                @endif
            </div>
            @if($playerUrl)
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content pv-modal-content">
                            <div class="modal-header pv-modal-header">
                                <div>
                                    <h2 class="modal-title fs-5" id="{{ $modalId }}-label">{{ $video->title }}</h2>
                                    <div class="pv-modal-teacher">{{ $video->teacher }}{{ $video->channel_name ? ' · '.$video->channel_name : '' }}</div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar aula"></button>
                            </div>
                            <div class="modal-body">
                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden bg-dark">
                                    <iframe title="{{ $video->title }}" data-topic-video-src="{{ $playerUrl }}" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
                                </div>
                                @if($video->description)
                                    <p class="small-muted mt-3 mb-0">{{ $video->description }}</p>
                                @endif
                            </div>
                            <div class="modal-footer"><button type="button" class="btn pv-btn-yellow" data-bs-dismiss="modal">Voltar à questão</button></div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </section>
@endif

@once
    @push('styles')
        <style>
            .pv-recommendation{border:1px solid #ead8ab!important;border-left:5px solid #f4c644!important;background:linear-gradient(115deg,#fffbf1,#fff 82%);box-shadow:0 14px 36px rgba(16,37,68,.08)}
            .pv-eyebrow{display:inline-flex;align-items:center;gap:.5rem;background:#ffe8a0;color:#4b3908;border-radius:999px;padding:.4rem .8rem;font-size:.72rem;font-weight:800;letter-spacing:.06em;margin-bottom:.85rem}
            .pv-recommendation-title{color:#102544;font-size:clamp(1.25rem,2vw,1.55rem)}
            .pv-lesson-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;padding:1rem .2rem;border-top:1px solid #e9e1ce}
            .pv-lesson-info{flex:1 1 220px;min-width:0}.pv-lesson-title{color:#112949;font-size:1.04rem}.pv-locked{color:#9a6113;font-size:.84rem;font-weight:700}
            .pv-btn-yellow{background:#f4c644!important;border:1px solid #e0aa19!important;color:#102544!important;border-radius:12px;padding:.62rem 1rem;font-weight:800;box-shadow:0 3px 0 #c89d24;transition:.15s}
            .pv-btn-yellow:hover,.pv-btn-yellow:focus{background:#ffdd72!important;color:#102544!important;transform:translateY(-1px)}
            .pv-btn-yellow:focus-visible{outline:3px solid #173b72;outline-offset:3px}
            .pv-modal-content{border:0;border-radius:20px;overflow:hidden;box-shadow:0 30px 80px rgba(0,16,42,.3)}
            .pv-modal-header{background:linear-gradient(110deg,#102544,#235d94);border-bottom:5px solid #f4c644;color:#fff;padding:1.25rem 1.5rem}
            .pv-modal-header .modal-title{color:#fff;font-weight:800}.pv-modal-teacher{color:#e1ebf8;margin-top:.25rem;font-size:.86rem}
            .pv-modal-content .modal-body{background:#f9fbfe;padding:1.35rem}.pv-modal-content .modal-footer{padding:1rem 1.35rem}
            @media(max-width:575.98px){.pv-lesson-row .pv-btn-yellow{width:100%}.pv-modal-content{border-radius:14px}.pv-modal-content .modal-body{padding:.7rem}.pv-recommendation{padding:1.15rem!important}}
        </style>
    @endpush
@endonce
@once
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.pv-modal-content').forEach(function (content) {
                const modal = content.closest('.modal');
                const frame = content.querySelector('iframe[data-topic-video-src]');
                if (!modal || !frame) return;
                modal.addEventListener('show.bs.modal', function () { frame.src = frame.dataset.topicVideoSrc; });
                modal.addEventListener('hidden.bs.modal', function () { frame.removeAttribute('src'); });
            });
        });
        </script>
    @endpush
@endonce
