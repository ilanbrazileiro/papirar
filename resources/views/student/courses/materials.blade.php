@extends('layouts.student')
@section('title', 'Biblioteca de '.$course->title)
@push('styles')
<style>
.course-library-head{background:linear-gradient(115deg,#102544,#1e4b80);border-radius:20px;padding:1.7rem 2rem;color:#fff;margin-bottom:1.5rem}.course-library-head h1{color:#fff;font-size:clamp(1.4rem,3vw,2rem);margin:.65rem 0}.course-library-tabs{display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:1.25rem}.course-library-tabs button{border:1px solid #b8cbe8;background:#fff;color:#14345d;border-radius:12px;font-weight:700;padding:.8rem 1.05rem;cursor:pointer}.course-library-tabs button[aria-selected=true]{background:#173b72;border-color:#173b72;color:white}.course-library-panel[hidden]{display:none!important}.course-library-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem}.course-library-item{background:#fff;border:1px solid #dae3ef;border-radius:16px;padding:1.3rem;display:flex;flex-direction:column;gap:.7rem;min-width:0}.course-library-item h2{font-size:1.13rem;line-height:1.3;margin:0;overflow-wrap:anywhere}.course-library-item .details{color:#66758a;font-size:.86rem}.course-library-actions{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:auto}.course-library-actions>a{flex:1 1 110px}.course-library-empty{background:#fff;border:1px dashed #b8cbe8;border-radius:16px;padding:2rem;color:#52647b}.course-library-badge{font-size:.78rem;font-weight:700;padding:.35rem .6rem;border-radius:8px;background:#edf4fc;color:#153f70;align-self:flex-start}.course-library-icon{font-size:1.65rem;font-weight:900;color:#174a87}@media(max-width:650px){.course-library-head{padding:1.3rem}.course-library-tabs{display:grid;grid-template-columns:1fr}.course-library-tabs button{width:100%;text-align:left}}

/* IDEIA-001: identidade visual da biblioteca */
.course-library-head{position:relative;overflow:hidden;border-bottom:5px solid #f4c644!important;box-shadow:0 18px 42px rgba(16,37,68,.16);background:linear-gradient(115deg,#102544,#215d92)!important}
.course-library-head::after{content:"";position:absolute;width:180px;height:180px;right:-56px;top:-95px;border-radius:50%;border:2px solid rgba(244,198,68,.45);pointer-events:none}
.course-library-head::before{content:"";position:absolute;width:80px;height:80px;right:80px;bottom:-46px;border-radius:50%;background:rgba(244,198,68,.12);pointer-events:none}
.course-library-head h1{font-weight:850;letter-spacing:-.035em}
.course-library-tabs{padding:.65rem;background:#fff;border:1px solid #e5e9f0;border-radius:16px;box-shadow:0 9px 30px rgba(16,37,68,.05)}
.course-library-tabs button{flex:1 1 auto;transition:.16s;border:1px solid #dbe5f2!important}
.course-library-tabs button[aria-selected=true]{color:#102544!important;background:#f4c644!important;border-color:#e4b634!important;box-shadow:0 3px 0 #c8a02b}
.course-library-tabs button:hover{border-color:#efc653!important;background:#fff9e9}
.course-video-section{border-color:#e4d4ac!important;background:#fffefa!important;box-shadow:0 12px 36px rgba(16,37,68,.05)}
.course-video-section-title{display:flex;align-items:center;gap:.8rem;margin-bottom:.5rem}
.course-video-section-title h2{font-size:clamp(1.03rem,2vw,1.3rem);font-weight:800;color:#102544}
.course-video-section-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:11px;background:#f4c644;color:#102544}
.course-video-card{background:#fff!important;border:1px solid #e7dfcc!important;box-shadow:0 8px 20px rgba(16,37,68,.055);transition:.15s}
.course-video-card:hover{transform:translateY(-2px);border-color:#e1bc57!important;box-shadow:0 16px 30px rgba(16,37,68,.1)}
.course-video-card-top{display:flex;align-items:center;justify-content:space-between;gap:1rem}
.course-video-play{display:grid;place-items:center;width:44px;height:44px;background:#102544;color:#f4c644;border-radius:13px;font-size:1.2rem}
.course-video-kind{font-size:.7rem;font-weight:850;letter-spacing:.05em;color:#6b5017;background:#fff3cd;padding:.3rem .65rem;border-radius:999px}
.course-video-title{font-size:1.06rem;line-height:1.35;color:#102544}
.course-video-cta{background:#f4c644!important;border-color:#e5b62a!important;color:#102544!important;border-radius:11px;font-weight:850;padding:.72rem 1rem;box-shadow:0 3px 0 #c89d24;text-decoration:none!important;margin-top:auto}
.course-video-cta:hover,.course-video-cta:focus{background:#ffdd77!important;color:#102544!important}
.course-video-locked{color:#865a11;font-size:.85rem;font-weight:700;background:#fff4d7;border-radius:9px;padding:.6rem}
@media(max-width:650px){.course-library-tabs{padding:.55rem}.course-library-tabs button{width:100%}.course-video-card{padding:1.05rem!important}}
</style>
@endpush
@section('content')
<div class="course-library-head">
    <a class="text-white" href="{{ route('student.courses.show',$course) }}">← Voltar ao curso</a>
    <h1>Biblioteca de {{ $course->title }}</h1>
    <p class="mb-0" style="color:#dfebf9">Explore materiais, provas anteriores e videoaulas selecionadas para sua preparação.</p>
</div>
@php
    $learningMaterials = $documents->where('type', 'commented_pdf');
    $pastExams = $documents->where('type', 'official_exam');
@endphp
<div class="course-library-tabs" role="tablist" aria-label="Biblioteca do curso">
    <button id="course-tab-materiais" type="button" role="tab" aria-selected="true" aria-controls="course-panel-materiais" data-course-tab="materiais">▤ Materiais do concurso ({{ $learningMaterials->count() }})</button>
    <button id="course-tab-provas" type="button" role="tab" aria-selected="false" aria-controls="course-panel-provas" data-course-tab="provas">▦ Provas anteriores ({{ $pastExams->count() }})</button>
    <button id="course-tab-aulas" type="button" role="tab" aria-selected="false" aria-controls="course-panel-aulas" data-course-tab="aulas">▶ Videoaulas</button>
</div>
@foreach(['materiais' => $learningMaterials, 'provas' => $pastExams] as $tabName => $entries)
<section id="course-panel-{{ $tabName }}" class="course-library-panel" role="tabpanel" aria-labelledby="course-tab-{{ $tabName }}" @if($tabName !== 'materiais') hidden @endif>
    @if($entries->isEmpty())
        <div class="course-library-empty">{{ $tabName === 'materiais' ? 'Nenhum material didático publicado para este concurso.' : 'Nenhuma prova anterior publicada para este concurso.' }}</div>
    @else
    <div class="course-library-grid">
    @foreach($entries as $document)
        @php
            $isExam = $document->type === 'official_exam';
            $detail = $isExam ? $document->publicUrl() : route('library.materials.show', ['document' => $document->id, 'slug' => \Illuminate\Support\Str::slug($document->title) ?: 'material-comentado']);
            $download = $isExam ? 'library.exams.download' : 'library.materials.download';
        @endphp
        <article class="course-library-item">
            <div class="d-flex align-items-center gap-2"><span class="course-library-icon" aria-hidden="true">{{ $isExam ? '▦' : '▤' }}</span><span class="course-library-badge">{{ $isExam ? 'Prova oficial' : 'Material didático' }}</span></div>
            <h2>{{ $document->title }}</h2>
            <div class="details">{{ $document->year ?: 'Ano não informado' }}{{ $document->exam_board ? ' · '.$document->exam_board : '' }}</div>
            @if($document->description)<p class="mb-1 text-secondary small">{{ \Illuminate\Support\Str::limit($document->description, 150) }}</p>@endif
            <div class="course-library-actions">
                <a class="btn btn-outline-primary btn-sm" href="{{ $detail }}">Detalhes</a>
                @if($allowed[$document->id] ?? false)
                    <a class="btn btn-primary btn-sm" href="{{ route($download, $document) }}">↓ Baixar PDF</a>
                @else
                    <span class="w-100 small text-secondary">Download disponível para acesso pago ativo ou manual autorizado.</span>
                @endif
            </div>
        </article>
    @endforeach
    </div>
    @endif
</section>
@endforeach
<section id="course-panel-aulas" class="course-library-panel" role="tabpanel" aria-labelledby="course-tab-aulas" hidden>
    @php
        $groupedTopicVideos = ($topicVideos ?? collect())->groupBy(function ($video) {
            return ($video->subject?->name ?? 'Disciplina') . ' / ' . ($video->topic?->name ?? 'Tópico');
        });
    @endphp

    @forelse($groupedTopicVideos as $group => $videos)
        <div class="course-library-item mb-3 course-video-section">
            <div class="course-video-section-title"><span class="course-video-section-icon">▶</span><h2 class="mb-0">{{ $group }}</h2></div>
            <div class="course-library-grid">
                @foreach($videos as $video)
                    <div class="course-library-item course-video-card">
                        <div class="course-video-card-top"><span class="course-video-play">▶</span><span class="course-video-kind">{{ $video->origin === 'youtube' ? 'AULA GRATUITA' : 'AULA EXCLUSIVA' }}</span></div>
                        <strong class="course-video-title">{{ $video->title }}</strong>
                        <div class="details">{{ $video->teacher }} · {{ $video->channel_name }}</div>
                        @if($video->description)
                            <p class="small text-secondary">{{ $video->description }}</p>
                        @endif
                        @if($video->origin === 'youtube')
                            <span class="course-library-badge">YouTube · gratuito com cadastro</span>
                        @else
                            <span class="course-library-badge">Papirar · exclusivo</span>
                        @endif
                        @if($videoAllowed[$video->id] ?? false)
                            <a href="{{ route('student.courses.topic-videos.show', [$course, $video]) }}" class="btn btn-sm course-video-cta">▶ Assistir no Papirar</a>
                        @else
                            <span class="course-video-locked">🔒 Disponível para assinantes deste curso</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="course-library-empty">
            <strong>Videoaulas em preparação</strong>
            <p>As aulas publicadas e vinculadas aos tópicos deste curso aparecerão aqui.</p>
        </div>
    @endforelse
</section>
@push('scripts')
<script>
(function(){
    const tabs = document.querySelectorAll('[data-course-tab]');
    if(!tabs.length) return;
    function activate(name, setHash){
        if(!['materiais','provas','aulas'].includes(name)) name = 'materiais';
        tabs.forEach(function(tab){const current = tab.dataset.courseTab === name;tab.setAttribute('aria-selected', current ? 'true' : 'false');tab.tabIndex=current?0:-1;});
        document.querySelectorAll('.course-library-panel').forEach(function(panel){panel.hidden = panel.id !== 'course-panel-'+name;});
        if(setHash) history.replaceState(null, '', '#'+name);
    }
    tabs.forEach(function(tab){tab.addEventListener('click',function(){activate(tab.dataset.courseTab,true);});tab.addEventListener('keydown',function(event){if(event.key!=='ArrowRight'&&event.key!=='ArrowLeft')return;event.preventDefault();const arr=Array.from(tabs),index=arr.indexOf(tab),next=arr[(index+(event.key==='ArrowRight'?1:-1)+arr.length)%arr.length];next.focus();activate(next.dataset.courseTab,true);});});
    activate(window.location.hash.replace('#',''),false);
    window.addEventListener('hashchange',function(){activate(window.location.hash.replace('#',''),false);});
})();
</script>
@endpush
@endsection
