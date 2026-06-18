<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\Question;
use App\Models\PaymentTransaction;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courseAccesses = CourseAccess::query()
            ->with(['course.corporation', 'course.exam'])
            ->where('user_id', Auth::id())
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('ends_at')
            ->get();

        $activeCourseIds = $courseAccesses
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $availableCourses = Course::query()
            ->active()
            ->public()
            ->whereNotIn('id', $activeCourseIds ?: [0])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $courseQuestionCounts = [];

        foreach ($courseAccesses as $access) {
            if ($access->course) {
                $courseQuestionCounts[$access->course_id] = $this->countQuestionsForCourse($access->course);
            }
        }

        foreach ($availableCourses as $course) {
            $courseQuestionCounts[$course->id] = $this->countQuestionsForCourse($course);
        }

        $paymentStatus = request('payment');

        $pendingTransactions = PaymentTransaction::query()
            ->with(['course', 'subscription'])
            ->where('user_id', Auth::id())
            ->whereNotNull('course_id')
            ->where('status', PaymentTransaction::STATUS_PENDING)
            ->latest('id')
            ->limit(5)
            ->get();

        $recentTransactions = PaymentTransaction::query()
            ->with(['course', 'subscription'])
            ->where('user_id', Auth::id())
            ->whereNotNull('course_id')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('student.courses.index', compact(
            'courseAccesses',
            'availableCourses',
            'courseQuestionCounts',
            'paymentStatus',
            'pendingTransactions',
            'recentTransactions'
        ));
    }

    public function show(Course $course): View
    {
        $access = $this->authorizeCourseAccess($course);

        $scope = $this->resolveCourseScope($course);

        $subjects = Subject::query()
            ->whereIn('id', $scope['subject_ids'])
            ->orderBy('name')
            ->get();

        $topics = Topic::query()
            ->whereIn('id', $scope['topic_ids'])
            ->orderBy('name')
            ->get();

        $sourceMaterials = SourceMaterial::query()
            ->whereIn('id', $scope['source_material_ids'])
            ->orderBy('title')
            ->get();

        $totalQuestions = $this->countQuestionsForCourse($course);

        $transactions = PaymentTransaction::query()
            ->with('subscription')
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->latest('id')
            ->limit(5)
            ->get();

        return view('student.courses.show', compact('course', 'access', 'subjects', 'topics', 'sourceMaterials', 'totalQuestions', 'transactions'));
    }

    public function study(Course $course): View
    {
        $this->authorizeCourseAccess($course);

        $scope = $this->resolveCourseScope($course);

        $subjects = Subject::query()
            ->whereIn('id', $scope['subject_ids'])
            ->orderBy('name')
            ->get();

        $topicsQuery = Topic::query()
            ->whereIn('subject_id', $scope['subject_ids']);

        if (!empty($scope['topic_ids'])) {
            $topicsQuery->whereIn('id', $scope['topic_ids']);
        }

        $topics = $topicsQuery
            ->orderBy('name')
            ->get()
            ->groupBy('subject_id');

        $sourceMaterials = SourceMaterial::query()
            ->whereIn('id', $scope['source_material_ids'])
            ->orderBy('title')
            ->get();

        return view('student.courses.study', compact('course', 'subjects', 'topics', 'sourceMaterials'));
    }

    private function authorizeCourseAccess(Course $course): CourseAccess
    {
        $access = CourseAccess::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('ends_at')
            ->first();

        abort_unless($access, 403);

        return $access;
    }

    private function countQuestionsForCourse(Course $course): int
    {
        $scope = $this->resolveCourseScope($course);

        if (empty($scope['subject_ids']) && empty($scope['topic_ids'])) {
            return 0;
        }

        return Question::query()
            ->where('status', 'published')
            ->when(!empty($scope['subject_ids']), fn ($q) => $q->whereIn('subject_id', $scope['subject_ids']))
            ->when(!empty($scope['topic_ids']), fn ($q) => $q->whereIn('topic_id', $scope['topic_ids']))
            ->when(!empty($scope['source_material_ids']), fn ($q) => $q->whereIn('source_material_id', $scope['source_material_ids']))
            ->count();
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            $subjectIds = DB::table('exam_subjects')
                ->where('exam_id', $course->exam_id)
                ->where('is_active', true)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $topicIds = DB::table('exam_subject_topics')
                ->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')
                ->where('exam_subjects.exam_id', $course->exam_id)
                ->where('exam_subjects.is_active', true)
                ->where('exam_subject_topics.is_active', true)
                ->pluck('exam_subject_topics.topic_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $sourceMaterialIds = DB::table('exam_subject_source_materials')
                ->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')
                ->where('exam_subjects.exam_id', $course->exam_id)
                ->where('exam_subjects.is_active', true)
                ->where('exam_subject_source_materials.is_active', true)
                ->pluck('exam_subject_source_materials.source_material_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            return [
                'subject_ids' => $subjectIds,
                'topic_ids' => $topicIds,
                'source_material_ids' => $sourceMaterialIds,
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
