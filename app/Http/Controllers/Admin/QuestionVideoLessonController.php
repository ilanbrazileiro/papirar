<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionVideoLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionVideoLessonController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));
        $provider = trim((string) $request->get('provider', ''));

        $lessons = QuestionVideoLesson::query()
            ->with(['question.subject', 'question.topic'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('video_url', 'like', "%{$search}%")
                        ->orWhereHas('question', function ($questionQuery) use ($search) {
                            $questionQuery->where('statement', 'like', "%{$search}%")
                                ->orWhere('id', $search);
                        });
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($provider !== '', fn ($q) => $q->where('provider', $provider))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.question-video-lessons.index', compact('lessons', 'search', 'status', 'provider'));
    }

    public function create(Request $request)
    {
        $question = null;

        if ($request->filled('question_id')) {
            $question = Question::query()->with(['subject', 'topic'])->find($request->integer('question_id'));
        }

        $lesson = new QuestionVideoLesson([
            'question_id' => $question?->id,
            'provider' => 'youtube',
            'visibility' => 'course_access',
            'status' => 'active',
        ]);

        return view('admin.question-video-lessons.create', [
            'lesson' => $lesson,
            'question' => $question,
            'questions' => $this->questionOptions($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if (empty($data['provider'])) {
            $data['provider'] = QuestionVideoLesson::detectProvider($data['video_url'] ?? '');
        }

        if (empty($data['embed_url'])) {
            $data['embed_url'] = QuestionVideoLesson::makeEmbedUrl($data['video_url'] ?? null, $data['provider']);
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $lesson = QuestionVideoLesson::query()->create($data);

        return redirect()
            ->route('admin.question-video-lessons.edit', $lesson)
            ->with('success', 'Aula da questão cadastrada com sucesso.');
    }

    public function edit(QuestionVideoLesson $questionVideoLesson)
    {
        $questionVideoLesson->load(['question.subject', 'question.topic']);

        return view('admin.question-video-lessons.edit', [
            'lesson' => $questionVideoLesson,
            'question' => $questionVideoLesson->question,
            'questions' => $this->questionOptions(null, $questionVideoLesson->question_id),
        ]);
    }

    public function update(Request $request, QuestionVideoLesson $questionVideoLesson): RedirectResponse
    {
        $data = $this->validatedData($request, $questionVideoLesson->id);

        if (empty($data['provider'])) {
            $data['provider'] = QuestionVideoLesson::detectProvider($data['video_url'] ?? '');
        }

        if (empty($data['embed_url'])) {
            $data['embed_url'] = QuestionVideoLesson::makeEmbedUrl($data['video_url'] ?? null, $data['provider']);
        }

        $data['updated_by'] = auth()->id();

        $questionVideoLesson->update($data);

        return redirect()
            ->route('admin.question-video-lessons.edit', $questionVideoLesson)
            ->with('success', 'Aula da questão atualizada com sucesso.');
    }

    public function destroy(QuestionVideoLesson $questionVideoLesson): RedirectResponse
    {
        $questionVideoLesson->delete();

        return redirect()
            ->route('admin.question-video-lessons.index')
            ->with('success', 'Aula da questão removida com sucesso.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'question_id' => [
                'required',
                'integer',
                'exists:questions,id',
                Rule::unique('question_video_lessons', 'question_id')->ignore($ignoreId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'provider' => ['required', Rule::in(['youtube', 'vimeo', 'external', 'html'])],
            'video_url' => ['nullable', 'url', 'max:1000'],
            'embed_url' => ['nullable', 'url', 'max:1000'],
            'thumbnail_url' => ['nullable', 'url', 'max:1000'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'visibility' => ['required', Rule::in(['course_access', 'public'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function questionOptions(?Request $request = null, ?int $selectedQuestionId = null)
    {
        $search = trim((string) ($request?->get('question_search') ?? ''));

        return Question::query()
            ->with(['subject', 'topic'])
            ->when($selectedQuestionId, fn ($q) => $q->orWhere('id', $selectedQuestionId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('statement', 'like', "%{$search}%")
                        ->orWhere('id', $search);
                });
            })
            ->orderByDesc('id')
            ->limit(80)
            ->get();
    }
}
