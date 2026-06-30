@extends('layouts.student')

@section('title', 'Questão')

@section('content')
    @php
        $difficultyLabels = [
            'easy' => 'Fácil',
            'medium' => 'Média',
            'hard' => 'Difícil',
        ];

        $userDifficultyVote = \App\Models\QuestionDifficultyVote::query()
            ->where('user_id', auth()->id())
            ->where('question_id', $question->id)
            ->first();

        $userPendingComment = \App\Models\QuestionComment::query()
            ->where('user_id', auth()->id())
            ->where('question_id', $question->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $currentDifficultyVote = old('difficulty_vote', $userDifficultyVote->difficulty_vote ?? null);
        $approvedComments = $question->comments ?? collect();
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Questão {{ $currentPosition }} de {{ $totalQuestions }}</h1>
            <p class="page-subtitle">
                {{ $session->course->title ?? 'Sessão de estudo' }}
                @if($question->subject) · {{ $question->subject->name }} @endif
                @if($question->topic) · {{ $question->topic->name }} @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('student.courses.questions.favorite', [$session->course_id, $question]) }}">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}#favorite-note-card">
                <button type="submit" class="btn {{ ($isFavorited ?? false) ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ ($isFavorited ?? false) ? '★ Favoritada' : '☆ Favoritar' }}
                </button>
            </form>
            <a href="{{ $session->course ? route('student.courses.show', $session->course) : route('student.courses.index') }}" class="btn btn-outline-primary">Voltar ao curso</a>
        </div>
    </div>

    @if($favorite ?? false)
        <div class="card-soft p-4 mb-4 border border-warning-subtle" id="favorite-note-card">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <div class="section-title mb-1">Anotação da favorita</div>
                    <p class="small-muted mb-0">Registre por que esta questão merece atenção. A anotação ficará salva na sua lista de favoritas.</p>
                </div>
                <a href="{{ route('student.courses.favorites.index', $session->course_id) }}" class="btn btn-sm btn-outline-secondary align-self-start">Ver favoritas</a>
            </div>

            <form method="POST" action="{{ route('student.courses.favorites.note', [$session->course_id, $question]) }}" class="mt-3">
                @csrf
                @method('PATCH')
                <textarea name="note" rows="3" class="form-control" maxlength="3000" placeholder="Ex.: Errei porque confundi o conceito; revisar antes da prova; questão boa para fixar este tópico.">{{ old('note', $favorite->note) }}</textarea>
                @error('note')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-sm-center mt-3">
                    <span class="small-muted">Você pode salvar a anotação e continuar o estudo normalmente.</span>
                    <button class="btn btn-outline-primary">Salvar anotação</button>
                </div>
            </form>
        </div>
    @endif

    <div class="question-card mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @if($question->difficulty)
                <span class="meta-badge">Dificuldade: {{ $difficultyLabels[$question->difficulty] ?? $question->difficulty }}</span>
            @endif
            @if($question->sourceMaterial)
                <span class="meta-badge">Fonte: {{ $question->sourceMaterial->title }}</span>
            @endif
            @if($question->activeVideoLesson)
                <span class="meta-badge">Aula em vídeo disponível após responder</span>
            @endif
            @if($isFavorited ?? false)
                <span class="meta-badge">Favoritada</span>
            @endif
        </div>

        <div class="mb-4 papirar-katex">
            {!! $question->statement !!}
        </div>

        @if($userAnswer)
            @foreach($question->alternatives->sortBy('letter') as $alternative)
                @php
                    $isSelected = (int) $userAnswer->selected_alternative_id === (int) $alternative->id;
                    $class = $alternative->is_correct ? 'correct' : ($isSelected ? 'wrong' : '');
                @endphp
                <div class="alt-card {{ $class }} mb-2">
                    <strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}
                </div>
            @endforeach

            <div class="card-soft p-4 mt-4">
                <div class="section-title">Comentário da questão</div>
                <div class="papirar-katex">{!! $question->commented_answer ?: 'Comentário ainda não cadastrado.' !!}</div>
            </div>

            @includeIf('student.courses.partials.video-lesson', ['question' => $question])

            <div class="row g-4 mt-1">
                <div class="col-lg-5">
                    <div class="card-soft p-4 h-100" id="difficulty-vote-card">
                        <div class="section-title mb-1">Como você avalia a dificuldade?</div>
                        <p class="small-muted mb-3">Sua votação ajuda a calibrar a percepção dos alunos sobre esta questão.</p>

                        <form method="POST" action="{{ route('student.questions.difficulty.store', $question) }}">
                            @csrf

                            <div class="d-grid gap-2">
                                @foreach($difficultyLabels as $value => $label)
                                    <label class="difficulty-option {{ $currentDifficultyVote === $value ? 'active' : '' }}">
                                        <input type="radio" name="difficulty_vote" value="{{ $value }}" {{ $currentDifficultyVote === $value ? 'checked' : '' }} required>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('difficulty_vote')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                            <button class="btn btn-outline-primary w-100 mt-3">
                                {{ $userDifficultyVote ? 'Atualizar voto' : 'Registrar voto' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card-soft p-4 h-100" id="student-comments-card">
                        <div class="section-title mb-1">Comentários dos alunos</div>
                        <p class="small-muted mb-3">Envie uma dúvida, observação ou contribuição. O comentário passa por moderação.</p>

                        @if($userPendingComment)
                            <div class="alert alert-warning mb-3">
                                <strong>Comentário em moderação.</strong><br>
                                {{ $userPendingComment->comment }}
                            </div>
                        @endif

                        @if($approvedComments->isNotEmpty())
                            <div class="d-grid gap-3 mb-4">
                                @foreach($approvedComments as $comment)
                                    <div class="student-comment-box">
                                        <div class="d-flex justify-content-between gap-2 mb-1">
                                            <strong>{{ $comment->user->name ?? 'Aluno' }}</strong>
                                            <span class="small-muted">{{ optional($comment->created_at)->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div>{{ $comment->comment }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="student-comment-empty mb-4">
                                Ainda não há comentários aprovados nesta questão.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('student.questions.comments.store', $question) }}">
                            @csrf
                            <label class="form-label fw-semibold">Adicionar comentário</label>
                            <textarea name="comment" rows="3" class="form-control @error('comment') is-invalid @enderror" minlength="3" maxlength="5000" required placeholder="Escreva sua dúvida ou contribuição sobre esta questão.">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-3">
                                <span class="small-muted">Após o envio, o comentário ficará pendente de aprovação.</span>
                                <button class="btn btn-outline-primary">Enviar comentário</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('student.course-study.next', $session) }}" class="mt-4 text-end">
                @csrf
                <button class="btn btn-primary">Próxima questão</button>
            </form>
        @else
            <form method="POST" action="{{ route('student.course-study.answer', $session) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <div class="small-muted mb-3">Use a tesoura para riscar alternativas que você quer eliminar antes de responder.</div>

                @foreach($question->alternatives->sortBy('letter') as $alternative)
                    <div class="alt-card d-flex align-items-start gap-2 mb-2 js-alternative-row" data-alt-id="{{ $alternative->id }}">
                        <button type="button" class="btn btn-sm btn-light border js-cut-alternative" title="Riscar alternativa" aria-label="Riscar alternativa">✂</button>
                        <label class="d-flex align-items-start gap-2 mb-0 flex-grow-1">
                            <input type="radio" name="selected_alternative_id" value="{{ $alternative->id }}" required class="mt-1">
                            <span><strong>{{ $alternative->letter }})</strong> {!! $alternative->text !!}</span>
                        </label>
                    </div>
                @endforeach

                <div class="text-end mt-4">
                    <button class="btn btn-primary">Responder</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .alternative-cut { opacity: .55; background: #f8f9fa; }
    .alternative-cut label span { text-decoration: line-through; }
    .difficulty-option { display: flex; align-items: center; gap: 10px; border: 1px solid var(--border); background: #fff; border-radius: 14px; padding: 12px 14px; cursor: pointer; transition: .15s ease; font-weight: 700; }
    .difficulty-option:hover { border-color: #AFC5E3; background: #F7FAFF; }
    .difficulty-option.active { border-color: var(--brand); background: #EEF5FF; color: var(--brand-dark); }
    .difficulty-option input { margin: 0; }
    .student-comment-box { border: 1px solid var(--border); background: #fff; border-radius: 14px; padding: 14px; }
    .student-comment-empty { border: 1px dashed var(--border); border-radius: 14px; padding: 14px; color: var(--muted); background: #fff; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'papirar-cut-question-{{ $question->id }}';
    const rows = Array.from(document.querySelectorAll('.js-alternative-row'));
    let cutIds = [];

    try { cutIds = JSON.parse(localStorage.getItem(storageKey) || '[]'); } catch (e) { cutIds = []; }

    function persist() { localStorage.setItem(storageKey, JSON.stringify(cutIds)); }

    rows.forEach(function (row) {
        const altId = row.dataset.altId;
        if (cutIds.includes(altId)) row.classList.add('alternative-cut');
        const button = row.querySelector('.js-cut-alternative');
        if (!button) return;
        button.addEventListener('click', function () {
            row.classList.toggle('alternative-cut');
            if (row.classList.contains('alternative-cut')) {
                if (!cutIds.includes(altId)) cutIds.push(altId);
            } else {
                cutIds = cutIds.filter(id => id !== altId);
            }
            persist();
        });
    });

    document.querySelectorAll('.difficulty-option input[type="radio"]').forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('.difficulty-option').forEach(function (label) { label.classList.remove('active'); });
            this.closest('.difficulty-option')?.classList.add('active');
        });
    });
});
</script>
@endpush
