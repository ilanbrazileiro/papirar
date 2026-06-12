<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\Questions\QuestionDuplicateChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));
        $difficulty = trim((string) $request->get('difficulty', ''));
        $corporationId = $request->integer('corporation_id');
        $subjectId = $request->integer('subject_id');
        $sourceMaterialId = $request->integer('source_material_id');

        $questions = Question::query()
            ->with(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('statement', 'like', "%{$search}%")
                        ->orWhere('source_reference', 'like', "%{$search}%")
                        ->orWhereHas('sourceMaterial', function ($sourceQuery) use ($search) {
                            $sourceQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('reference_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($difficulty !== '', fn ($q) => $q->where('difficulty', $difficulty))
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($sourceMaterialId, fn ($q) => $q->where('source_material_id', $sourceMaterialId))
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
            'sourceMaterialId' => $sourceMaterialId,
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'sourceMaterials' => SourceMaterial::query()
                ->with(['corporation', 'subject'])
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function create()
    {
        $question = new Question([
            'question_type' => 'multiple_choice',
            'difficulty' => 'medium',
            'source_type' => 'authored',
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
            'sourceMaterials' => SourceMaterial::query()->active()->orderBy('title')->get(),
            'selectedExam' => null,
            'selectedTopic' => null,
            'selectedSourceMaterial' => null,
        ]);
    }

    public function store(Request $request, QuestionDuplicateChecker $duplicateChecker): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($duplicateChecker->hasExactDuplicate($data['statement'])) {
            throw ValidationException::withMessages([
                'statement' => 'Já existe uma questão cadastrada com este mesmo enunciado. Verifique a duplicidade antes de salvar.',
            ]);
        }

        DB::transaction(function () use ($data) {
            $question = Question::query()->create([
                'corporation_id' => $data['corporation_id'] ?? null,
                'exam_id' => $data['exam_id'] ?? null,
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'statement' => $data['statement'],
                'question_type' => $data['question_type'],
                'difficulty' => $data['difficulty'],
                'source_type' => $data['source_type'],
                'source_reference' => $data['source_reference'] ?? null,
                'source_material_id' => $data['source_material_id'] ?? null,
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
        $question->load(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial', 'alternatives']);

        return view('admin.questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        $question->load(['alternatives', 'sourceMaterial']);

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
            'sourceMaterials' => SourceMaterial::query()->active()->orderBy('title')->get(),
            'selectedExam' => $question->exam,
            'selectedTopic' => $question->topic,
            'selectedSourceMaterial' => $question->sourceMaterial,
        ]);
    }

    public function update(Request $request, Question $question, QuestionDuplicateChecker $duplicateChecker): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($duplicateChecker->hasExactDuplicate($data['statement'], $question->id)) {
            throw ValidationException::withMessages([
                'statement' => 'Já existe outra questão cadastrada com este mesmo enunciado. Verifique a duplicidade antes de salvar.',
            ]);
        }

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'corporation_id' => $data['corporation_id'] ?? null,
                'exam_id' => $data['exam_id'] ?? null,
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'statement' => $data['statement'],
                'question_type' => $data['question_type'],
                'difficulty' => $data['difficulty'],
                'source_type' => $data['source_type'],
                'source_reference' => $data['source_reference'] ?? null,
                'source_material_id' => $data['source_material_id'] ?? null,
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

    public function ajaxSourceMaterials(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('q', ''));
        $corporationId = $request->integer('corporation_id');
        $subjectId = $request->integer('subject_id');
        $examId = $request->integer('exam_id');

        $query = SourceMaterial::query()
            ->with(['corporation', 'subject'])
            ->where('active', true)
            ->when($corporationId, function ($q) use ($corporationId) {
                $q->where(function ($inner) use ($corporationId) {
                    $inner->where('corporation_id', $corporationId)
                        ->orWhereNull('corporation_id');
                });
            })
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($examId && $subjectId, function ($q) use ($examId, $subjectId) {
                $q->where(function ($inner) use ($examId, $subjectId) {
                    $inner->whereHas('examSubjectLinks', function ($link) use ($examId, $subjectId) {
                        $link->where('is_active', true)
                            ->whereHas('examSubject', function ($examSubject) use ($examId, $subjectId) {
                                $examSubject->where('exam_id', $examId)
                                    ->where('subject_id', $subjectId);
                            });
                    })->orWhereDoesntHave('examSubjectLinks');
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('reference_code', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->limit(30);

        $results = $query->get()
            ->map(function (SourceMaterial $material) {
                $details = collect([
                    optional($material->corporation)->name,
                    optional($material->subject)->name,
                    $material->year,
                    $material->reference_code,
                ])->filter()->implode(' | ');

                return [
                    'id' => $material->id,
                    'text' => $details ? "{$material->title} ({$details})" : $material->title,
                ];
            })
            ->values();

        return response()->json(['results' => $results]);
    }

    private function validatedData(Request $request): array
    {
        $request->merge([
            'corporation_id' => $this->emptyToNull($request->input('corporation_id')),
            'exam_id' => $this->emptyToNull($request->input('exam_id')),
            'topic_id' => $this->emptyToNull($request->input('topic_id')),
            'source_material_id' => $this->emptyToNull($request->input('source_material_id')),
            'question_type' => $request->input('question_type') ?: 'multiple_choice',
            'source_type' => $request->input('source_type') === 'official_exam' ? 'exam' : $request->input('source_type'),
        ]);

        $validated = $request->validate([
            'corporation_id' => ['nullable', 'integer', 'exists:corporations,id'],
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'source_material_id' => ['nullable', 'integer', 'exists:source_materials,id'],
            'statement' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['multiple_choice'])],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'source_type' => ['required', Rule::in(['exam', 'authored', 'adapted'])],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'commented_answer' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'alternatives' => ['required', 'array', 'size:5'],
            'alternatives.*.letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'alternatives.*.text' => ['required', 'string'],
            'correct_letter' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
        ]);

        $this->validateSourceMaterialConsistency($validated);

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

    private function validateSourceMaterialConsistency(array $validated): void
    {
        if (empty($validated['source_material_id'])) {
            return;
        }

        $material = SourceMaterial::query()->findOrFail($validated['source_material_id']);

        if ((int) $material->subject_id !== (int) $validated['subject_id']) {
            throw ValidationException::withMessages([
                'source_material_id' => 'A fonte/material selecionada não pertence à disciplina informada.',
            ]);
        }

        if (!empty($validated['corporation_id']) && $material->corporation_id && (int) $material->corporation_id !== (int) $validated['corporation_id']) {
            throw ValidationException::withMessages([
                'source_material_id' => 'A fonte/material selecionada pertence a outra corporação.',
            ]);
        }
    }

    private function emptyToNull(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }
}
