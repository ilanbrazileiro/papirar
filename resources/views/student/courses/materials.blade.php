@extends('layouts.student')
@section('title', 'Biblioteca de '.$course->title)
@push('styles')
<style>
.course-library-head{background:linear-gradient(115deg,#102544,#1e4b80);border-radius:20px;padding:1.7rem 2rem;color:#fff;margin-bottom:1.5rem}.course-library-head h1{color:#fff;font-size:clamp(1.4rem,3vw,2rem);margin:.65rem 0}.course-library-tabs{display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:1.25rem}.course-library-tabs button{border:1px solid #b8cbe8;background:#fff;color:#14345d;border-radius:12px;font-weight:700;padding:.8rem 1.05rem;cursor:pointer}.course-library-tabs button[aria-selected=true]{background:#173b72;border-color:#173b72;color:white}.course-library-panel[hidden]{display:none!important}.course-library-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem}.course-library-item{background:#fff;border:1px solid #dae3ef;border-radius:16px;padding:1.3rem;display:flex;flex-direction:column;gap:.7rem;min-width:0}.course-library-item h2{font-size:1.13rem;line-height:1.3;margin:0;overflow-wrap:anywhere}.course-library-item .details{color:#66758a;font-size:.86rem}.course-library-actions{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:auto}.course-library-actions>a{flex:1 1 110px}.course-library-empty{background:#fff;border:1px dashed #b8cbe8;border-radius:16px;padding:2rem;color:#52647b}.course-library-badge{font-size:.78rem;font-weight:700;padding:.35rem .6rem;border-radius:8px;background:#edf4fc;color:#153f70;align-self:flex-start}.course-library-icon{font-size:1.65rem;font-weight:900;color:#174a87}@media(max-width:650px){.course-library-head{padding:1.3rem}.course-library-tabs{display:grid;grid-template-columns:1fr}.course-library-tabs button{width:100%;text-align:left}}
</style>
@endpush
@section('content')
<div class="course-library-head">
    <a class="text-white" href="{{ route('student.courses.show',$course) }}">← Voltar ao curso</a>
    <h1>Biblioteca de {{ $course->title }}</h1>
    <p class="mb-0" style="color:#dfebf9">Materiais didáticos, provas anteriores e espaço para futuras aulas.</p>
</div>
@php
    $learningMaterials = $documents->where('type', 'commented_pdf');
    $pastExams = $documents->where('type', 'official_exam');
@endphp
<div class="course-library-tabs" role="tablist" aria-label="Biblioteca do curso">
    <button id="course-tab-materiais" type="button" role="tab" aria-selected="true" aria-controls="course-panel-materiais" data-course-tab="materiais">▤ Materiais do concurso ({{ $learningMaterials->count() }})</button>
    <button id="course-tab-provas" type="button" role="tab" aria-selected="false" aria-controls="course-panel-provas" data-course-tab="provas">▦ Provas anteriores ({{ $pastExams->count() }})</button>
    <button id="course-tab-aulas" type="button" role="tab" aria-selected="false" aria-controls="course-panel-aulas" data-course-tab="aulas">▷ Aulas</button>
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
    <div class="course-library-empty"><strong>Aulas em breve</strong><p class="mb-0 mt-2">Esta seção está reservada para aulas futuras de {{ $course->title }}. Nenhuma aula está disponível neste momento.</p></div>
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
