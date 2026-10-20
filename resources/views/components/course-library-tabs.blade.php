@php
    $learningMaterials = $documents->where('type', 'commented_pdf');
    $pastExams = $documents->where('type', 'official_exam');
@endphp
<section class="card-soft p-4 mb-4" id="recursos-do-curso" aria-label="Recursos do curso">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <div class="section-title mb-1">Biblioteca do curso</div>
            <p class="small-muted mb-0">Consulte materiais didáticos, provas anteriores e o espaço de aulas.</p>
        </div>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('student.courses.materials', $course) }}">Ver biblioteca completa</a>
    </div>
    <div class="papirar-course-tabs" role="tablist" aria-label="Conteúdos do concurso">
        <button type="button" id="resource-tab-materiais" role="tab" aria-selected="true" aria-controls="resource-panel-materiais" data-resource-tab="materiais"><span aria-hidden="true">▤</span> Materiais do concurso <span class="papirar-tab-count">{{ $learningMaterials->count() }}</span></button>
        <button type="button" id="resource-tab-provas" role="tab" aria-selected="false" aria-controls="resource-panel-provas" data-resource-tab="provas"><span aria-hidden="true">▦</span> Provas anteriores <span class="papirar-tab-count">{{ $pastExams->count() }}</span></button>
        <button type="button" id="resource-tab-aulas" role="tab" aria-selected="false" aria-controls="resource-panel-aulas" data-resource-tab="aulas"><span aria-hidden="true">▷</span> Aulas</button>
    </div>
    @foreach(['materiais' => $learningMaterials, 'provas' => $pastExams] as $group => $entries)
        <div class="papirar-resource-panel" id="resource-panel-{{ $group }}" role="tabpanel" aria-labelledby="resource-tab-{{ $group }}" @if($group !== 'materiais') hidden @endif>
            @forelse($entries as $doc)
                @php
                    $isExam = $doc->type === 'official_exam';
                    $pageUrl = $isExam ? $doc->publicUrl() : route('library.materials.show', ['document' => $doc->id, 'slug' => \Illuminate\Support\Str::slug($doc->title) ?: 'material-comentado']);
                    $downloadRoute = $isExam ? 'library.exams.download' : 'library.materials.download';
                @endphp
                <article class="papirar-resource-card">
                    <span class="papirar-resource-glyph" aria-hidden="true">{{ $isExam ? '▦' : '▤' }}</span>
                    <div class="papirar-resource-details">
                        <h3>{{ $doc->title }}</h3>
                        <span>{{ $doc->year ?: 'Ano não informado' }}{{ $doc->exam_board ? ' · '.$doc->exam_board : '' }}</span>
                        @if($doc->description)<p>{{ \Illuminate\Support\Str::limit($doc->description, 110) }}</p>@endif
                    </div>
                    <div class="papirar-resource-actions">
                        <a class="btn btn-sm btn-outline-primary" href="{{ $pageUrl }}">Detalhes</a>
                        @if($allowed[$doc->id] ?? false)
                            <a class="btn btn-sm btn-primary" href="{{ route($downloadRoute, $doc) }}">↓ Baixar PDF</a>
                        @else
                            <span class="small-muted" title="Requer acesso pago ativo ou permissão manual">Download exclusivo para assinantes</span>
                        @endif
                    </div>
                </article>
            @empty
                <p class="papirar-resources-empty">{{ $group === 'materiais' ? 'Nenhum material didático publicado para este curso.' : 'Nenhuma prova anterior publicada para este curso.' }}</p>
            @endforelse
        </div>
    @endforeach
    <div id="resource-panel-aulas" class="papirar-resource-panel" role="tabpanel" aria-labelledby="resource-tab-aulas" hidden>
        <p class="papirar-resources-empty"><strong>Aulas em breve.</strong> Este espaço receberá as aulas de {{ $course->title }} quando forem disponibilizadas.</p>
    </div>
</section>
@once
@push('styles')
<style>
.papirar-course-tabs{display:flex;flex-wrap:wrap;gap:.55rem;border-bottom:1px solid #dde7f3;padding-bottom:1rem;margin-bottom:1.2rem}.papirar-course-tabs button{background:#f2f6fc;color:#12355e;border:1px solid #d6e2f2;border-radius:10px;padding:.75rem 1rem;font-weight:750;cursor:pointer}.papirar-course-tabs button[aria-selected="true"]{background:#173b72;color:white;border-color:#173b72}.papirar-tab-count{display:inline-block;padding:.05rem .38rem;background:#ffffff2b;border:1px solid #d5e1f0;border-radius:12px;font-size:.8rem}.papirar-resource-panel[hidden]{display:none!important}.papirar-resource-card{display:flex;gap:1rem;align-items:center;background:#f8fafd;border:1px solid #dde7f3;border-radius:12px;padding:1rem;margin-bottom:.7rem}.papirar-resource-glyph{background:#e2edfc;color:#174a87;border-radius:10px;padding:.7rem;font-size:1.5rem}.papirar-resource-details{flex:1;min-width:0}.papirar-resource-details h3{font-size:1rem;margin:0 0 .3rem;overflow-wrap:anywhere}.papirar-resource-details span,.papirar-resource-details p{color:#66758a;font-size:.83rem;margin:0}.papirar-resource-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;max-width:245px}.papirar-resources-empty{border:1px dashed #bccde5;border-radius:12px;padding:1.6rem;background:#f8fafd;color:#52647b;margin:0}@media(max-width:740px){.papirar-course-tabs{display:grid;grid-template-columns:1fr}.papirar-course-tabs button{text-align:left}.papirar-resource-card{align-items:flex-start;flex-wrap:wrap}.papirar-resource-actions{width:100%;max-width:none}}
</style>
@endpush
@push('scripts')
<script>
(function(){
  const tabs = Array.from(document.querySelectorAll('[data-resource-tab]'));
  if(!tabs.length)return;
  function choose(name){
    if(!['materiais','provas','aulas'].includes(name))name='materiais';
    tabs.forEach(function(tab){const selected=tab.dataset.resourceTab===name;tab.setAttribute('aria-selected',selected?'true':'false');tab.tabIndex=selected?0:-1;});
    document.querySelectorAll('.papirar-resource-panel').forEach(function(panel){panel.hidden=panel.id!=='resource-panel-'+name;});
  }
  tabs.forEach(function(tab,i){
    tab.addEventListener('click',function(){choose(tab.dataset.resourceTab);});
    tab.addEventListener('keydown',function(event){if(event.key!=='ArrowRight'&&event.key!=='ArrowLeft')return;event.preventDefault();const next=tabs[(i+(event.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length];next.focus();choose(next.dataset.resourceTab);});
  });
})();
</script>
@endpush
@endonce
