<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));
        $difficulty = trim((string) $request->get('difficulty', ''));
        $corporationId = $request->integer('corporation_id');
        $subjectId = $request->integer('subject_id');

        $questions = Question::query()
            ->with(['corporation', 'exam', 'subject', 'topic'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('statement', 'like', "%{$search}%")
                        ->orWhere('source_reference', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($difficulty !== '', fn ($q) => $q->where('difficulty', $difficulty))
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.questions.index', [
            'questions' => $questions,
            'search' => $search,
            'status' => $status,
            'difficulty' => $difficulty,
            'corporationId' => $corporationId,
            'subjectId' => $subjectId,
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $question = new Question([
            'question_type' => 'multiple_choice',
            'difficulty' => 'medium',
            'source_type' => 'official_exam',
            'status' => 'draft',
        ]);

        $question->setRelation('alternatives', collect([
            new Alternative(['letter' => 'A']),
            new Alternative(['letter' => 'B']),
            new Alternative(['letter' => 'C']),
            new Alternative(['letter' => 'D']),
            new Alternative(['letter' => 'E']),
        ]));

        return view('admin.questions.create', [
            'question' => $question,
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'selectedExam' => null,
            'selectedTopic' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            $question = Question::query()->create([
                'corporation_id' => $data['corporation_id'],
                'exam_id' => $data['exam_id'] ?? null,
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'statement' => $data['statement'],
                'question_type' => $data['question_type'],
                'difficulty' => $data['difficulty'],
                'source_type' => $data['source_type'],
                'source_reference' => $data['source_reference'] ?? null,
                'commented_answer' => $data['commented_answer'] ?? null,
                'status' => $data['status'],
                'created_by' => auth()->id(),
            ]);

            foreach ($data['alternatives'] as $alternative) {
                $question->alternatives()->create([
                    'letter' => $alternative['letter'],
                    'text' => $alternative['text'],
                    'is_correct' => (bool) $alternative['is_correct'],
                ]);
            }
        });

        return redirect()->route('admin.questions.index')->with('success', 'Questão criada com sucesso.');
    }

    public function show(Question $question)
    {
        $question->load(['corporation', 'exam', 'subject', 'topic', 'alternatives']);

        return view('admin.questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        $question->load('alternatives');
        $alternatives = $question->alternatives->sortBy('letter')->values();
        if ($alternatives->count() < 5) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
                if (!$alternatives->firstWhere('letter', $letter)) {
                    $alternatives->push(new Alternative(['letter' => $letter]));
                }
            }
            $alternatives = $alternatives->sortBy('letter')->values();
            $question->setRelation('alternatives', $alternatives);
        }

        return view('admin.questions.edit', [
            'question' => $question,
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'selectedExam' => $question->exam,
            'selectedTopic' => $question->topic,
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'corporation_id' => $data['corporation_id'],
                'exam_id' => $data['exam_id'] ?? null,
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'statement' => $data['statement'],
                'question_type' => $data['question_type'],
                'difficulty' => $data['difficulty'],
                'source_type' => $data['source_type'],
                'source_reference' => $data['source_reference'] ?? null,
                'commented_answer' => $data['commented_answer'] ?? null,
                'status' => $data['status'],
            ]);

            $question->alternatives()->delete();

            foreach ($data['alternatives'] as $alternative) {
                $question->alternatives()->create([
                    'letter' => $alternative['letter'],
                    'text' => $alternative['text'],
                    'is_correct' => (bool) $alternative['is_correct'],
                ]);
            }
        });

        return redirect()->route('admin.questions.edit', $question)->with('success', 'Questão atualizada com sucesso.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.questions.index')->with('success', 'Questão removida com sucesso.');
    }

    public function ajaxExams(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('q', ''));
        $corporationId = $request->integer('corporation_id');

        $results = Exam::query()
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('exam_type', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('year')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn ($exam) => [
                'id' => $exam->id,
                'text' => "{$exam->title} ({$exam->year}) - {$exam->exam_type}",
            ])
            ->values();

        return response()->json(['results' => $results]);
    }

    public function ajaxTopics(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('q', ''));
        $subjectId = $request->integer('subject_id');

        $results = Topic::query()
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn ($topic) => [
                'id' => $topic->id,
                'text' => $topic->name,
            ])
            ->values();

        return response()->json(['results' => $results]);
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'statement' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['multiple_choice'])],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'source_type' => ['required', Rule::in(['official_exam', 'authored', 'adapted'])],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'commented_answer' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'alternatives' => ['required', 'array', 'size:5'],
            'alternatives.*.letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'alternatives.*.text' => ['required', 'string'],
            'correct_letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
        ]);

        $validated['alternatives'] = collect($validated['alternatives'])
            ->map(function ($alternative) use ($validated) {
                return [
                    'letter' => $alternative['letter'],
                    'text' => $alternative['text'],
                    'is_correct' => $alternative['letter'] === $validated['correct_letter'],
                ];
            })
            ->all();

        return $validated;
    }
}
