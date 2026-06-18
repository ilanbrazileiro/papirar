@extends('layouts.student')

@section('title', 'Favoritas - ' . $course->title)

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Questões favoritas</h1>
            <p class="page-subtitle">{{ $course->title }} · questões marcadas para revisão.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Estudar favoritas</a>
            <a href="{{ route('student.courses.show', $course) }}" class="btn btn-outline-primary">Voltar ao curso</a>
        </div>
    </div>

    <div class="card-soft p-4">
        @if($favorites->isEmpty())
            <div class="text-center py-5">
                <div class="section-title">Nenhuma questão favorita ainda</div>
                <p class="small-muted mb-4">Durante o estudo, clique em “Favoritar” nas questões importantes.</p>
                <a href="{{ route('student.courses.study', $course) }}" class="btn btn-primary">Iniciar estudo</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Questão</th>
                            <th>Disciplina</th>
                            <th>Tópico</th>
                            <th>Fonte</th>
                            <th>Anotação</th>
                            <th>Aula</th>
                            <th>Favoritada em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($favorites as $favorite)
                            @php($question = $favorite->question)
                            <tr>
                                <td class="fw-semibold">#{{ $question->id ?? '-' }}</td>
                                <td>{{ $question->subject->name ?? '-' }}</td>
                                <td>{{ $question->topic->name ?? '-' }}</td>
                                <td>{{ $question->sourceMaterial->title ?? '-' }}</td>
                                <td style="min-width: 220px;">
                                    @if($favorite->note)
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($favorite->note, 120) }}</div>
                                    @else
                                        <span class="text-muted">Sem anotação</span>
                                    @endif
                                </td>
                                <td>
                                    @if($question?->activeVideoLesson)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ optional($favorite->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    @if($question)
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('student.courses.favorites.show', [$course, $question]) }}" class="btn btn-sm btn-outline-primary">
                                                Ver gabarito
                                            </a>
                                            <form method="POST" action="{{ route('student.courses.favorites.retry', [$course, $question]) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-primary">Refazer</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $favorites->links() }}
            </div>
        @endif
    </div>
@endsection
