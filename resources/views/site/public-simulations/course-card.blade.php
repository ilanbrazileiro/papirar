@php
    $course = $sim->course;
    $courseUrl = $course->landing_enabled
        ? route('site.course-landings.show', $course->slug)
        : route('site.courses.show', $course->slug);
    $courseCover = $course->coverImageUrl();
@endphp
<aside class="ps-course" aria-label="Curso relacionado ao simulado">
    <div class="ps-course-cover">
        @if($courseCover)
            <img src="{{ $courseCover }}" alt="Capa do curso {{ $course->title }}" loading="lazy">
        @else
            <div class="ps-course-placeholder"><span>P</span><small>PAPIRAR CONCURSOS</small></div>
        @endif
    </div>
    <div class="ps-course-copy">
        <span class="ps-eyebrow">CONTINUE SUA PREPARAÇÃO</span>
        <h2>{{ $course->title }}</h2>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($course->commercialHeadline()), 170) }}</p>
        <ul class="ps-course-benefits">
            <li>Questões direcionadas e comentadas</li>
            <li>Simulados para praticar e acompanhar sua evolução</li>
            <li>Estude no seu ritmo, pelo computador ou celular</li>
        </ul>
        <a class="ps-button ps-button-primary" href="{{ $courseUrl }}">Conhecer o curso <span aria-hidden="true">→</span></a>
    </div>
</aside>
