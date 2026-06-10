<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\ExamSubjectTopic;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $corporationId = $request->integer('corporation_id');
        $year = $request->integer('year');

        $exams = Exam::query()
            ->with('corporation')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('exam_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($corporationId, fn ($q) => $q->where('corporation_id', $corporationId))
            ->when($year, fn ($q) => $q->where('year', $year))
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        $corporations = Corporation::query()->orderBy('name')->get();
        $years = Exam::query()->select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('admin.exams.index', compact('exams', 'corporations', 'years', 'search', 'corporationId', 'year'));
    }

    public function create()
    {
        $exam = new Exam([
            'active' => true,
            'status' => Exam::STATUS_PUBLISHED,
        ]);

        return view('admin.exams.create', $this->formData($exam));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data) {
            $exam = Exam::create($data);
            $this->syncSubjectsAndTopics($exam, $request);
        });

        return redirect()
            ->route('admin.exams.index')
            ->with('success', 'Concurso cadastrado com sucesso.');
    }

    public function show(Exam $exam)
    {
        $exam->load([
            'corporation',
            'examSubjects.subject',
            'examSubjects.topicLinks.topic',
        ]);

        return view('admin.exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $exam->load(['examSubjects.topicLinks']);

        return view('admin.exams.edit', $this->formData($exam));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $this->validatedData($request, $exam->id);

        DB::transaction(function () use ($request, $exam, $data) {
            $exam->update($data);
            $this->syncSubjectsAndTopics($exam, $request);
        });

        return redirect()
            ->route('admin.exams.edit', $exam)
            ->with('success', 'Concurso atualizado com sucesso.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()
            ->route('admin.exams.index')
            ->with('success', 'Concurso removido com sucesso.');
    }

    private function formData(Exam $exam): array
    {
        $corporations = Corporation::query()->orderBy('name')->get();

        $subjects = Subject::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $topicsBySubject = Topic::query()
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('subject_id');

        $examSubjects = collect();
        $selectedSubjects = [];
        $selectedTopicsBySubject = [];

        if ($exam->exists) {
            $examSubjects = ExamSubject::query()
                ->with('topicLinks')
                ->where('exam_id', $exam->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $selectedSubjects = $examSubjects
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($examSubjects as $examSubject) {
                $selectedTopicsBySubject[(int) $examSubject->subject_id] = $examSubject->topicLinks
                    ->where('is_active', true)
                    ->pluck('topic_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        return compact(
            'exam',
            'corporations',
            'subjects',
            'topicsBySubject',
            'selectedSubjects',
            'selectedTopicsBySubject',
            'examSubjects'
        );
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'title' => ['required', 'string', 'max:180'],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'exam_type' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'in:' . Exam::STATUS_PLANNED . ',' . Exam::STATUS_PUBLISHED],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'subjects' => ['nullable', 'array'],
            'subjects.*.selected' => ['nullable', 'boolean'],
            'subjects.*.topics' => ['nullable', 'array'],
            'subjects.*.topics.*' => ['integer', 'exists:topics,id'],
        ]);

        $exists = Exam::query()
            ->where('corporation_id', $validated['corporation_id'])
            ->where('title', $validated['title'])
            ->where('year', $validated['year'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(back()->withErrors(['title' => 'Já existe um concurso com esse título, corporação e ano.'])->withInput());
        }

        return [
            'corporation_id' => (int) $validated['corporation_id'],
            'title' => $validated['title'],
            'year' => (int) $validated['year'],
            'exam_type' => $validated['exam_type'],
            'status' => $validated['status'] ?? Exam::STATUS_PUBLISHED,
            'description' => $validated['description'] ?? null,
            'active' => (bool) ($validated['active'] ?? false),
        ];
    }

    private function syncSubjectsAndTopics(Exam $exam, Request $request): void
    {
        $subjectsInput = $request->input('subjects', []);

        $selectedSubjectIds = collect($subjectsInput)
            ->filter(fn ($data) => (bool) ($data['selected'] ?? false))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $validSubjectIds = Subject::query()
            ->whereIn('id', $selectedSubjectIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $currentExamSubjects = ExamSubject::query()
            ->where('exam_id', $exam->id)
            ->get()
            ->keyBy('subject_id');

        $subjectsToRemove = $currentExamSubjects
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->diff($validSubjectIds)
            ->all();

        if (! empty($subjectsToRemove)) {
            ExamSubject::query()
                ->where('exam_id', $exam->id)
                ->whereIn('subject_id', $subjectsToRemove)
                ->delete();
        }

        foreach ($validSubjectIds as $index => $subjectId) {
            $examSubject = ExamSubject::query()->updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'subject_id' => $subjectId,
                ],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );

            $topicIds = collect($subjectsInput[$subjectId]['topics'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $validTopicIds = Topic::query()
                ->where('subject_id', $subjectId)
                ->whereIn('id', $topicIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            ExamSubjectTopic::query()
                ->where('exam_subject_id', $examSubject->id)
                ->delete();

            foreach ($validTopicIds as $topicIndex => $topicId) {
                ExamSubjectTopic::create([
                    'exam_subject_id' => $examSubject->id,
                    'topic_id' => $topicId,
                    'is_active' => true,
                    'sort_order' => $topicIndex + 1,
                ]);
            }
        }
    }
}
