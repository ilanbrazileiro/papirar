<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\StudySession;
use App\Models\StudySessionQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamStudyController extends Controller
{
    public function index(): View
    {
        return $this->create();
    }

    public function create(): View
    {
        $corporations = Corporation::query()
            ->where('active', true)
            ->whereHas('exams', function ($query) {
                $query->where('active', true);
            })
            ->orderBy('name')
            ->get();

        return view('student.study.exam-filter', compact('corporations'));
    }

    public function examsByCorporation(int $corporationId): JsonResponse
    {
        $exams = Exam::query()
            ->where('corporation_id', $corporationId)
            ->where('active', true)
            ->whereIn('status', [Exam::STATUS_PLANNED, Exam::STATUS_PUBLISHED])
            ->orderByRaw("CASE WHEN status = 'planned' THEN 0 ELSE 1 END")
            ->orderByDesc('year')
            ->orderBy('title')
            ->get(['id', 'title', 'year', 'status']);

        return response()->json($exams->map(fn (Exam $exam) => [
            'id' => $exam->id,
            'title' => $exam->title,
            'year' => $exam->year,
            'status' => $exam->status,
            'status_label' => $exam->status === Exam::STATUS_PLANNED ? 'Previsto' : 'Publicado',
        ])->values());
    }

    public function subjectsByExam(int $examId): JsonResponse
    {
        $exam = Exam::query()
            ->where('active', true)
            ->findOrFail($examId);

        $subjects = DB::table('exam_subjects')
            ->join('subjects', 'subjects.id', '=', 'exam_subjects.subject_id')
            ->where('exam_subjects.exam_id', $exam->id)
            ->where('exam_subjects.is_active', true)
            ->where('subjects.active', true)
            ->orderBy('exam_subjects.sort_order')
            ->orderBy('subjects.name')
            ->get([
                'exam_subjects.id as exam_subject_id',
                'subjects.id',
                'subjects.name',
                'subjects.scope',
            ]);

        $examSubjectIds = $subjects->pluck('exam_subject_id')->map(fn ($id) => (int) $id)->values();

        $topicsByExamSubject = DB::table('exam_subject_topics')
            ->join('topics', 'topics.id', '=', 'exam_subject_topics.topic_id')
            ->whereIn('exam_subject_topics.exam_subject_id', $examSubjectIds)
            ->where('exam_subject_topics.is_active', true)
            ->where('topics.active', true)
            ->orderBy('exam_subject_topics.sort_order')
            ->orderBy('topics.name')
            ->get([
                'exam_subject_topics.exam_subject_id',
                'topics.id',
                'topics.name',
            ])
            ->groupBy('exam_subject_id');

        return response()->json(
            $subjects->map(function ($subject) use ($topicsByExamSubject) {
                $topics = $topicsByExamSubject
                    ->get($subject->exam_subject_id, collect())
                    ->map(fn ($topic) => [
                        'id' => (int) $topic->id,
                        'name' => $topic->name,
                    ])
                    ->values();

                return [
                    'id' => (int) $subject->id,
                    'exam_subject_id' => (int) $subject->exam_subject_id,
                    'name' => $subject->name,
                    'scope' => $subject->scope ?? 'general',
                    'scope_label' => ($subject->scope ?? 'general') === 'corporation_specific'
                        ? 'Específica da corporação'
                        : 'Geral / reaproveitável',
                    'topics' => $topics,
                ];
            })->values()
        );
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'corporation_id' => ['required', 'integer', 'exists:corporations,id'],
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'topic_ids' => ['nullable', 'array'],
            'topic_ids.*' => ['array'],
            'topic_ids.*.*' => ['integer', 'exists:topics,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'mode' => ['required', 'in:train,exam,review'],
        ], [
            'corporation_id.required' => 'Selecione a corporação.',
            'exam_id.required' => 'Selecione o concurso.',
            'subject_ids.required' => 'Selecione pelo menos uma disciplina para estudar.',
            'subject_ids.min' => 'Selecione pelo menos uma disciplina para estudar.',
        ]);

        $exam = Exam::query()
            ->where('corporation_id', $data['corporation_id'])
            ->where('active', true)
            ->findOrFail($data['exam_id']);

        $examSubjects = DB::table('exam_subjects')
            ->where('exam_id', $exam->id)
            ->where('is_active', true)
            ->get(['id', 'subject_id']);

        $allowedSubjectIds = $examSubjects->pluck('subject_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $selectedSubjectIds = array_values(array_intersect(
            array_map('intval', $data['subject_ids']),
            $allowedSubjectIds
        ));

        if (empty($selectedSubjectIds)) {
            return back()
                ->withErrors(['subject_ids' => 'As disciplinas selecionadas não pertencem ao concurso escolhido.'])
                ->withInput();
        }

        $examSubjectIdsBySubject = $examSubjects
            ->whereIn('subject_id', $selectedSubjectIds)
            ->mapWithKeys(fn ($examSubject) => [(int) $examSubject->subject_id => (int) $examSubject->id]);

        $allowedTopicsBySubject = DB::table('exam_subject_topics')
            ->join('topics', 'topics.id', '=', 'exam_subject_topics.topic_id')
            ->join('exam_subjects', 'exam_subjects.id', '=', 'exam_subject_topics.exam_subject_id')
            ->whereIn('exam_subject_topics.exam_subject_id', $examSubjectIdsBySubject->values())
            ->where('exam_subject_topics.is_active', true)
            ->where('topics.active', true)
            ->get([
                'exam_subjects.subject_id',
                'exam_subject_topics.topic_id',
            ])
            ->groupBy('subject_id')
            ->map(fn ($rows) => $rows->pluck('topic_id')->map(fn ($id) => (int) $id)->unique()->values()->all());

        $selectedTopicIdsBySubject = [];
        $selectedTopicIds = [];
        $submittedTopics = $data['topic_ids'] ?? [];

        foreach ($selectedSubjectIds as $subjectId) {
            $allowedTopicIds = $allowedTopicsBySubject->get($subjectId, []);

            if (empty($allowedTopicIds)) {
                continue;
            }

            $submittedForSubject = array_map('intval', $submittedTopics[$subjectId] ?? []);
            $validTopicIds = array_values(array_intersect($submittedForSubject, $allowedTopicIds));

            if (empty($validTopicIds)) {
                return back()
                    ->withErrors(['topic_ids' => 'Selecione pelo menos um tópico para cada disciplina marcada.'])
                    ->withInput();
            }

            $selectedTopicIdsBySubject[$subjectId] = $validTopicIds;
            $selectedTopicIds = array_merge($selectedTopicIds, $validTopicIds);
        }

        $selectedTopicIds = array_values(array_unique(array_map('intval', $selectedTopicIds)));

        $questionsQuery = Question::query()
            ->select('questions.*')
            ->join('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->where('questions.status', 'published')
            ->whereIn('questions.subject_id', $selectedSubjectIds)
            ->where(function ($query) use ($data) {
                $query->where(function ($general) {
                    $general->where('subjects.scope', 'general')
                        ->orWhereNull('subjects.scope');
                })->orWhere(function ($specific) use ($data) {
                    $specific->where('subjects.scope', 'corporation_specific')
                        ->where('questions.corporation_id', (int) $data['corporation_id']);
                });
            });

        if (!empty($selectedTopicIds)) {
            $questionsQuery->whereIn('questions.topic_id', $selectedTopicIds);
        }

        if ($data['mode'] === 'review') {
            $questionsQuery->whereHas('answers', function ($answer) {
                $answer->where('user_id', auth()->id())
                    ->where('is_correct', false);
            });
        }

        $questions = $questionsQuery
            ->with(['subject', 'topic'])
            ->inRandomOrder()
            ->limit((int) $data['quantity'])
            ->get();

        if ($questions->isEmpty()) {
            return back()
                ->with('error', 'Nenhuma questão publicada foi encontrada para o concurso, disciplinas e tópicos selecionados.')
                ->withInput();
        }

        $session = StudySession::query()->create([
            'user_id' => auth()->id(),
            'corporation_id' => (int) $data['corporation_id'],
            'exam_id' => (int) $data['exam_id'],
            'subject_id' => count($selectedSubjectIds) === 1 ? $selectedSubjectIds[0] : null,
            'topic_id' => count($selectedTopicIds) === 1 ? $selectedTopicIds[0] : null,
            'source_material_id' => null,
            'mode' => $data['mode'] === 'exam' ? 'exam' : 'train',
            'started_at' => now(),
        ]);

        foreach ($questions->values() as $index => $question) {
            StudySessionQuestion::query()->create([
                'study_session_id' => $session->id,
                'question_id' => $question->id,
                'position' => $index + 1,
            ]);
        }

        return redirect()
            ->route('student.study.question', $session)
            ->with('success', 'Sessão criada com base no concurso, disciplinas e tópicos selecionados.');
    }
}
