<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\StudySession;
use App\Models\StudySessionQuestion;
use App\Models\UserAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseStudyController extends Controller
{
    public function start(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourseAccess($course);

        $scope = $this->resolveCourseScope($course);

        $data = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'source_material_id' => ['nullable', 'integer', 'exists:source_materials,id'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'mode' => ['required', 'in:train,review'],
        ]);

        if (!empty($data['subject_id']) && !in_array((int) $data['subject_id'], $scope['subject_ids'], true)) {
            return back()->with('error', 'A disciplina selecionada não pertence a este curso.')->withInput();
        }

        if (!empty($data['topic_id']) && !in_array((int) $data['topic_id'], $scope['topic_ids'], true)) {
            return back()->with('error', 'O tópico selecionado não pertence a este curso.')->withInput();
        }

        if (!empty($data['source_material_id']) && !in_array((int) $data['source_material_id'], $scope['source_material_ids'], true)) {
            return back()->with('error', 'A fonte selecionada não pertence a este curso.')->withInput();
        }

        $questions = Question::query()
            ->where('status', 'published')
            ->when(!empty($scope['subject_ids']), fn ($q) => $q->whereIn('subject_id', $scope['subject_ids']))
            ->when(!empty($scope['topic_ids']), fn ($q) => $q->whereIn('topic_id', $scope['topic_ids']))
            ->when(!empty($scope['source_material_ids']), fn ($q) => $q->whereIn('source_material_id', $scope['source_material_ids']))
            ->when(!empty($data['subject_id']), fn ($q) => $q->where('subject_id', $data['subject_id']))
            ->when(!empty($data['topic_id']), fn ($q) => $q->where('topic_id', $data['topic_id']))
            ->when(!empty($data['source_material_id']), fn ($q) => $q->where('source_material_id', $data['source_material_id']))
            ->when(!empty($data['difficulty']), fn ($q) => $q->where('difficulty', $data['difficulty']))
            ->when($data['mode'] === 'review', function ($q) {
                $q->whereHas('answers', function ($answer) {
                    $answer->where('user_id', Auth::id())
                        ->where('is_correct', false);
                });
            })
            ->inRandomOrder()
            ->limit($data['quantity'])
            ->get();

        if ($questions->isEmpty()) {
            return back()->with('error', 'Nenhuma questão encontrada dentro deste curso com os filtros informados.')->withInput();
        }

        $session = StudySession::query()->create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'corporation_id' => $course->corporation_id,
            'exam_id' => $course->exam_id,
            'subject_id' => $data['subject_id'] ?? null,
            'topic_id' => $data['topic_id'] ?? null,
            'source_material_id' => $data['source_material_id'] ?? null,
            'mode' => $data['mode'],
            'started_at' => now(),
        ]);

        foreach ($questions->values() as $index => $question) {
            StudySessionQuestion::query()->create([
                'study_session_id' => $session->id,
                'question_id' => $question->id,
                'position' => $index + 1,
            ]);
        }

        return redirect()->route('student.course-study.question', $session);
    }

    public function showQuestion(StudySession $session)
    {
        $this->authorizeSessionAccess($session);

        $currentItem = StudySessionQuestion::query()
            ->where('study_session_id', $session->id)
            ->whereNull('answered_at')
            ->orderBy('position')
            ->first();

        if (!$currentItem) {
            return redirect()->route('student.course-study.result', $session);
        }

        $question = Question::query()
            ->with([
                'corporation',
                'exam',
                'subject',
                'topic',
                'sourceMaterial',
                'alternatives',
                'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
                'comments.user',
                'difficultyVotes',
            ])
            ->findOrFail($currentItem->question_id);

        return view('student.courses.question', [
            'session' => $session,
            'question' => $question,
            'currentItem' => $currentItem,
            'currentPosition' => $currentItem->position,
            'totalQuestions' => StudySessionQuestion::query()->where('study_session_id', $session->id)->count(),
            'userAnswer' => null,
        ]);
    }

    public function answer(Request $request, StudySession $session)
    {
        $this->authorizeSessionAccess($session);

        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_alternative_id' => ['required', 'integer', 'exists:alternatives,id'],
        ]);

        $currentItem = StudySessionQuestion::query()
            ->where('study_session_id', $session->id)
            ->where('question_id', $data['question_id'])
            ->firstOrFail();

        $question = Question::query()
            ->with([
                'corporation',
                'exam',
                'subject',
                'topic',
                'sourceMaterial',
                'alternatives',
                'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
                'comments.user',
                'difficultyVotes',
            ])
            ->findOrFail($data['question_id']);

        $selectedAlternative = $question->alternatives->firstWhere('id', $data['selected_alternative_id']);
        abort_if(!$selectedAlternative, 422, 'Alternativa inválida para esta questão.');

        UserAnswer::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'question_id' => $question->id,
                'study_session_id' => $session->id,
            ],
            [
                'selected_alternative_id' => $selectedAlternative->id,
                'is_correct' => (bool) $selectedAlternative->is_correct,
                'answered_at' => now(),
            ]
        );

        $currentItem->update(['answered_at' => now()]);

        return redirect()->route('student.course-study.review', [
            'session' => $session->id,
            'question' => $question->id,
        ]);
    }

    public function review(StudySession $session, Question $question)
    {
        $this->authorizeSessionAccess($session);

        $currentItem = StudySessionQuestion::query()
            ->where('study_session_id', $session->id)
            ->where('question_id', $question->id)
            ->firstOrFail();

        $question->load([
            'corporation',
            'exam',
            'subject',
            'topic',
            'sourceMaterial',
            'alternatives',
            'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
            'comments.user',
            'difficultyVotes',
        ]);

        $userAnswer = UserAnswer::query()
            ->where('study_session_id', $session->id)
            ->where('question_id', $question->id)
            ->where('user_id', Auth::id())
            ->first();

        return view('student.courses.question', [
            'session' => $session,
            'question' => $question,
            'currentItem' => $currentItem,
            'currentPosition' => $currentItem->position,
            'totalQuestions' => StudySessionQuestion::query()->where('study_session_id', $session->id)->count(),
            'userAnswer' => $userAnswer,
        ]);
    }

    public function next(StudySession $session): RedirectResponse
    {
        $this->authorizeSessionAccess($session);

        return redirect()->route('student.course-study.question', $session);
    }

    public function result(StudySession $session)
    {
        $this->authorizeSessionAccess($session);

        $answers = UserAnswer::query()
            ->with(['question.subject', 'question.topic', 'question.sourceMaterial'])
            ->where('study_session_id', $session->id)
            ->where('user_id', Auth::id())
            ->get();

        $total = StudySessionQuestion::query()->where('study_session_id', $session->id)->count();
        $correct = $answers->where('is_correct', true)->count();
        $incorrect = $answers->where('is_correct', false)->count();
        $accuracy = $total > 0 ? ($correct / $total) * 100 : 0;

        return view('student.courses.result', compact('session', 'answers', 'total', 'correct', 'incorrect', 'accuracy'));
    }

    private function authorizeSessionAccess(StudySession $session): void
    {
        abort_unless($session->user_id === Auth::id(), 403);
        abort_unless($session->course_id, 403);

        $hasAccess = CourseAccess::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $session->course_id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->exists();

        abort_unless($hasAccess, 403);
    }

    private function authorizeCourseAccess(Course $course): void
    {
        $hasAccess = CourseAccess::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->exists();

        abort_unless($hasAccess, 403);
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            return [
                'subject_ids' => DB::table('exam_subjects')
                    ->where('exam_id', $course->exam_id)
                    ->where('is_active', true)
                    ->pluck('subject_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
                'topic_ids' => DB::table('exam_subject_topics')
                    ->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')
                    ->where('exam_subjects.exam_id', $course->exam_id)
                    ->where('exam_subjects.is_active', true)
                    ->where('exam_subject_topics.is_active', true)
                    ->pluck('exam_subject_topics.topic_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all(),
                'source_material_ids' => DB::table('exam_subject_source_materials')
                    ->where('exam_id', $course->exam_id)
                    ->where('is_active', true)
                    ->pluck('source_material_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all(),
            ];
        }

        return [
            'subject_ids' => DB::table('course_subjects')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'topic_ids' => DB::table('course_topics')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('topic_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'source_material_ids' => DB::table('course_source_materials')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('source_material_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
        ];
    }
}
