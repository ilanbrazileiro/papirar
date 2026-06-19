<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\Question;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $activeCourseAccesses = CourseAccess::query()
            ->with(['course.corporation', 'course.exam'])
            ->where('user_id', $user->id)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('ends_at')
            ->get();

        $activeCourseIds = $activeCourseAccesses
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

        $allCoursesForRenewal = Course::query()
            ->active()
            ->public()
            ->whereIn('id', $activeCourseIds ?: [0])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->keyBy('id');

        $pendingTransactions = PaymentTransaction::query()
            ->with(['course', 'subscription'])
            ->where('user_id', $user->id)
            ->whereNotNull('course_id')
            ->where('status', PaymentTransaction::STATUS_PENDING)
            ->latest('id')
            ->limit(5)
            ->get();

        $recentTransactions = PaymentTransaction::query()
            ->with(['course', 'subscription'])
            ->where('user_id', $user->id)
            ->whereNotNull('course_id')
            ->latest('id')
            ->limit(5)
            ->get();

        $courseQuestionCounts = [];

        foreach ($activeCourseAccesses as $access) {
            if ($access->course) {
                $courseQuestionCounts[$access->course_id] = $this->countQuestionsForCourse($access->course);
            }
        }

        foreach ($availableCourses as $course) {
            $courseQuestionCounts[$course->id] = $this->countQuestionsForCourse($course);
        }

        $paymentStatus = $request->query('payment') ?: $request->query('status');

        return view('student.subscriptions.index', [
            'activeCourseAccesses' => $activeCourseAccesses,
            'availableCourses' => $availableCourses,
            'allCoursesForRenewal' => $allCoursesForRenewal,
            'pendingTransactions' => $pendingTransactions,
            'recentTransactions' => $recentTransactions,
            'courseQuestionCounts' => $courseQuestionCounts,
            'paymentStatus' => $paymentStatus,
            'needsEmailVerification' => ! $user->hasVerifiedEmail(),
        ]);
    }

    /**
     * Rota antiga de checkout de assinatura geral.
     *
     * O modelo atual do Papirar vende acesso por curso. Os botões da tela de assinatura
     * devem usar student.courses.checkout, que recebe o curso e o ciclo de cobrança.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'billing_cycle' => ['required', 'string'],
        ]);

        return redirect()
            ->route('student.subscriptions.index')
            ->with('info', 'Escolha o curso e o período diretamente nos cards de assinatura.');
    }

    public function history(): View
    {
        $subscriptions = Subscription::query()
            ->with(['course', 'transactions' => function ($query) {
                $query->latest('id');
            }])
            ->where('user_id', Auth::id())
            ->whereNotNull('course_id')
            ->latest('id')
            ->paginate(15);

        return view('student.subscriptions.history', compact('subscriptions'));
    }

    public function retry(Subscription $subscription): RedirectResponse
    {
        $user = Auth::user();

        abort_unless((int) $subscription->user_id === (int) $user->id, 403);

        if ($subscription->course_id) {
            return redirect()
                ->route('student.subscriptions.index')
                ->with('info', 'Para renovar ou ampliar, escolha novamente o curso e o período desejado.');
        }

        return redirect()
            ->route('student.subscriptions.index')
            ->with('error', 'Essa assinatura pertence ao modelo antigo e não pode ser renovada por aqui.');
    }

    private function countQuestionsForCourse(Course $course): int
    {
        $scope = $this->resolveCourseScope($course);

        if (empty($scope['subject_ids']) && empty($scope['topic_ids'])) {
            return 0;
        }

        return Question::query()
            ->whereIn('status', ['published', 'reviewed'])
            ->when(!empty($scope['subject_ids']), fn ($query) => $query->whereIn('subject_id', $scope['subject_ids']))
            ->when(!empty($scope['topic_ids']), fn ($query) => $query->whereIn('topic_id', $scope['topic_ids']))
            ->when(!empty($scope['source_material_ids']), fn ($query) => $query->whereIn('source_material_id', $scope['source_material_ids']))
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

        $subjectIds = DB::table('course_subjects')
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $topicIds = DB::table('course_topics')
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->pluck('topic_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $sourceMaterialIds = DB::table('course_source_materials')
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->pluck('source_material_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return [
            'subject_ids' => $subjectIds,
            'topic_ids' => $topicIds,
            'source_material_ids' => $sourceMaterialIds,
        ];
    }
}
