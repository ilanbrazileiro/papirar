@if(isset($question) && $question && $question->exists && \Illuminate\Support\Facades\Route::has('admin.questions.preview'))
    <a href="{{ route('admin.questions.preview', $question) }}" target="_blank" rel="noopener" class="btn btn-info">
        <i class="fas fa-eye"></i> Visualizar como aluno
    </a>
@endif
