<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\QuestionFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseFavoriteController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorizeCourseAccess($course);

        $favorites = QuestionFavorite::query()
            ->with(['question.subject', 'question.topic', 'question.sourceMaterial'])
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->latest('id')
            ->paginate(20);

        return view('student.courses.favorites', compact('course', 'favorites'));
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

    private function authorizeQuestionInCourse(Course $course, Question $question): void
    {
        $scope = $this->resolveCourseScope($course);

        if (!empty($scope['subject_ids']) && !in_array((int) $question->subject_id, $scope['subject_ids'], true)) {
            abort(403);
        }

        if (!empty($scope['topic_ids']) && $question->topic_id && !in_array((int) $question->topic_id, $scope['topic_ids'], true)) {
            abort(403);
        }

        if (!empty($scope['source_material_ids']) && $question->source_material_id && !in_array((int) $question->source_material_id, $scope['source_material_ids'], true)) {
            abort(403);
        }
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
                    ->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')
                    ->where('exam_subjects.exam_id', $course->exam_id)
                    ->where('exam_subjects.is_active', true)
                    ->where('exam_subject_source_materials.is_active', true)
                    ->pluck('exam_subject_source_materials.source_material_id')
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
