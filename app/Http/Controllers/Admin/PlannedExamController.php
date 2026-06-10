<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\ExamSubjectTopic;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlannedExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::query()
            ->with(['corporation', 'plannedSubjects'])
            ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])
            ->latest('id')
            ->paginate(20);

        return view('admin.planned-exams.index', compact('exams'));
    }

    public function create(): View
    {
        $exam = new Exam([
            'status' => Exam::STATUS_PLANNED,
            'active' => true,
        ]);

        return view('admin.planned-exams.create', $this->formData($exam));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($request, $data) {
            $exam = Exam::query()->create([
                'corporation_id' => $data['corporation_id'],
                'title' => $data['title'],
                'year' => $data['year'],
                'exam_type' => $data['exam_type'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'active' => $request->boolean('active', true),
            ]);

            $this->syncSubjectsAndTopics($exam, $data['subject_ids'], $request->input('topic_ids', []));
        });

        return redirect()
            ->route('admin.planned-exams.index')
            ->with('success', 'Concurso cadastrado com sucesso.');
    }

    public function edit(Exam $planned_exam): View
    {
        $exam = $planned_exam->load([
            'examSubjects.subject',
            'examSubjects.examSubjectTopics.topic',
            'plannedSubjects',
        ]);

        return view('admin.planned-exams.edit', $this->formData($exam));
    }

    public function update(Request $request, Exam $planned_exam): RedirectResponse
    {
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($request, $data, $planned_exam) {
            $planned_exam->update([
                'corporation_id' => $data['corporation_id'],
                'title' => $data['title'],
                'year' => $data['year'],
                'exam_type' => $data['exam_type'],
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'active' => $request->boolean('active', true),
            ]);

            $this->syncSubjectsAndTopics($planned_exam, $data['subject_ids'], $request->input('topic_ids', []));
        });

        return redirect()
            ->route('admin.planned-exams.index')
            ->with('success', 'Concurso atualizado com sucesso.');
    }

    private function formData(Exam $exam): array
    {
        $exam->loadMissing(['examSubjects.examSubjectTopics']);

        $selectedSubjects = $exam->examSubjects
            ->where('is_active', true)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $selectedTopicsBySubject = [];

        foreach ($exam->examSubjects as $examSubject) {
            $selectedTopicsBySubject[(int) $examSubject->subject_id] = $examSubject->examSubjectTopics
                ->where('is_active', true)
                ->pluck('topic_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return [
            'exam' => $exam,
            'corporations' => Corporation::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'subjects' => Subject::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'topicsBySubject' => Topic::query()
                ->where('active', true)
                ->orderBy('name')
                ->get()
                ->groupBy('subject_id'),
            'selectedSubjects' => $selectedSubjects,
            'selectedTopicsBySubject' => $selectedTopicsBySubject,
        ];
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'title' => ['required', 'string', 'max:180'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'exam_type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:planned,published'],
            'description' => ['nullable', 'string'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'topic_ids' => ['nullable', 'array'],
            'topic_ids.*' => ['nullable', 'array'],
            'topic_ids.*.*' => ['integer', 'exists:topics,id'],
        ], [
            'corporation_id.required' => 'Selecione a corporação.',
            'title.required' => 'Informe o nome do concurso.',
            'year.required' => 'Informe o ano.',
            'exam_type.required' => 'Informe o tipo do concurso.',
            'status.required' => 'Informe se o concurso é previsto ou publicado.',
            'subject_ids.required' => 'Selecione pelo menos uma disciplina.',
            'subject_ids.min' => 'Selecione pelo menos uma disciplina.',
        ]);
    }

    private function syncSubjectsAndTopics(Exam $exam, array $subjectIds, array $topicIdsBySubject): void
    {
        $subjectIds = collect($subjectIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        ExamSubject::query()
            ->where('exam_id', $exam->id)
            ->whereNotIn('subject_id', $subjectIds)
            ->delete();

        foreach ($subjectIds as $index => $subjectId) {
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

            $topicIds = collect($topicIdsBySubject[$subjectId] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($topicIds->isNotEmpty()) {
                $validTopicIds = Topic::query()
                    ->where('subject_id', $subjectId)
                    ->whereIn('id', $topicIds->all())
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            } else {
                $validTopicIds = [];
            }

            ExamSubjectTopic::query()
                ->where('exam_subject_id', $examSubject->id)
                ->whereNotIn('topic_id', $validTopicIds ?: [0])
                ->delete();

            foreach ($validTopicIds as $topicIndex => $topicId) {
                ExamSubjectTopic::query()->updateOrCreate(
                    [
                        'exam_subject_id' => $examSubject->id,
                        'topic_id' => $topicId,
                    ],
                    [
                        'sort_order' => $topicIndex + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
