<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\QuestionFavorite;
use App\Models\StudySession;
use App\Models\StudySessionQuestion;
use App\Models\UserAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseFavoriteController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorizeCourseAccess($course);

        $favorites = QuestionFavorite::query()
            ->with(['question.subject', 'question.topic', 'question.sourceMaterial', 'question.activeVideoLesson'])
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->whereHas('question', fn ($q) => $q->visibleToStudent())
            ->latest('id')
            ->paginate(20);

        return view('student.courses.favorites', compact('course', 'favorites'));
    }

    public function show(Course $course, Question $question): View
    {
        $this->authorizeCourseAccess($course);
        $this->authorizeQuestionInCourse($course, $question);
        $this->authorizeFavorite($course, $question);

        $favorite = QuestionFavorite::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('question_id', $question->id)
            ->firstOrFail();

        $question = $this->loadQuestion((int) $question->id);

        $lastAnswer = UserAnswer::query()
            ->with('selectedAlternative')
            ->where('user_id', Auth::id())
            ->where('question_id', $question->id)
            ->latest('answered_at')
            ->latest('id')
            ->first();

        $answerStats = UserAnswer::query()
            ->where('user_id', Auth::id())
            ->where('question_id', $question->id)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->first();

        return view('student.courses.favorite-question', compact('course', 'question', 'favorite', 'lastAnswer', 'answerStats'));
    }

    public function retry(Course $course, Question $question): RedirectResponse
    {
        $this->authorizeCourseAccess($course);
        $this->authorizeQuestionInCourse($course, $question);
        $this->authorizeFavorite($course, $question);

        $session = StudySession::query()->create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'corporation_id' => $course->corporation_id,
            'exam_id' => $course->exam_id,
            'subject_id' => $question->subject_id,
            'topic_id' => $question->topic_id,
            'source_material_id' => $question->source_material_id,
            'mode' => 'train',
            'started_at' => now(),
        ]);

        StudySessionQuestion::query()->create([
            'study_session_id' => $session->id,
            'question_id' => $question->id,
            'position' => 1,
        ]);

        return redirect()->route('student.course-study.question', $session);
    }

    public function updateNote(Request $request, Course $course, Question $question): RedirectResponse
    {
        $this->authorizeCourseAccess($course);
        $this->authorizeQuestionInCourse($course, $question);

        $data = $request->validate(['note' => ['nullable', 'string', 'max:3000']]);

        $favorite = QuestionFavorite::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('question_id', $question->id)
            ->firstOrFail();

        $favorite->update(['note' => $data['note'] ?? null]);

        return back()->with('success', 'Anotação da questão favorita atualizada.');
    }

    public function toggle(Course $course, Question $question): RedirectResponse
    {
        $this->authorizeCourseAccess($course);
        $this->authorizeQuestionInCourse($course, $question);

        $favorite = QuestionFavorite::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('question_id', $question->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Questão removida das favoritas.');
        }

        QuestionFavorite::query()->create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'question_id' => $question->id,
        ]);

        return back()->with('success', 'Questão adicionada às favoritas.');
    }

    private function loadQuestion(int $questionId): Question
    {
        return Question::query()
            ->with(['corporation', 'exam', 'subject', 'topic', 'sourceMaterial', 'alternatives', 'comments' => fn ($q) => $q->where('status', 'approved')->latest(), 'comments.user', 'difficultyVotes', 'activeVideoLesson'])
            ->visibleToStudent()
            ->findOrFail($questionId);
    }

    private function authorizeFavorite(Course $course, Question $question): void
    {
        $isFavorite = QuestionFavorite::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('question_id', $question->id)
            ->exists();

        abort_unless($isFavorite, 404);
    }

    private function authorizeCourseAccess(Course $course): void
    {
        $hasAccess = CourseAccess::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->exists();

        abort_unless($hasAccess, 403);
    }

    private function authorizeQuestionInCourse(Course $course, Question $question): void
    {
        abort_unless(in_array($question->status, Question::STUDENT_VISIBLE_STATUSES, true), 404);

        $scope = $this->resolveCourseScope($course);

        if (!empty($scope['subject_ids']) && !in_array((int) $question->subject_id, $scope['subject_ids'], true)) abort(403);
        if (!empty($scope['topic_ids']) && $question->topic_id && !in_array((int) $question->topic_id, $scope['topic_ids'], true)) abort(403);
        if (!empty($scope['source_material_ids']) && $question->source_material_id && !in_array((int) $question->source_material_id, $scope['source_material_ids'], true)) abort(403);
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            return [
                'subject_ids' => DB::table('exam_subjects')->where('exam_id', $course->exam_id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->values()->all(),
                'topic_ids' => DB::table('exam_subject_topics')->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_topics.is_active', true)->pluck('exam_subject_topics.topic_id')->map(fn ($id) => (int) $id)->unique()->values()->all(),
                'source_material_ids' => DB::table('exam_subject_source_materials')->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_source_materials.is_active', true)->pluck('exam_subject_source_materials.source_material_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all(),
            ];
        }

        return [
            'subject_ids' => DB::table('course_subjects')->where('course_id', $course->id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->values()->all(),
            'topic_ids' => DB::table('course_topics')->where('course_id', $course->id)->where('is_active', true)->pluck('topic_id')->map(fn ($id) => (int) $id)->values()->all(),
            'source_material_ids' => DB::table('course_source_materials')->where('course_id', $course->id)->where('is_active', true)->pluck('source_material_id')->map(fn ($id) => (int) $id)->values()->all(),
        ];
    }
}
