<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\QuestionFavorite;
use App\Models\SimulatedExam;
use App\Models\StudySession;
use App\Models\SupportTicket;
use App\Models\UserAnswer;
use App\Services\Study\PendingErrorReviewService;
use App\Services\Study\DailyMissionService;
use App\Services\Study\StudyPlanService;
use App\Services\Billing\TrialLifecycleService;
use App\Services\Study\AdaptiveStudyService;
use App\Services\Billing\RevenueProtectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PendingErrorReviewService $pendingErrors,
        private readonly DailyMissionService $dailyMissions,
        private readonly StudyPlanService $studyPlans,
        private readonly TrialLifecycleService $trialLifecycle,
        private readonly AdaptiveStudyService $adaptiveStudy,
        private readonly RevenueProtectionService $revenueProtection
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $userId = $user->id;

        $activeCourseAccesses = CourseAccess::query()
            ->with('course')
            ->where('user_id', $userId)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('ends_at')
            ->limit(6)
            ->get();

        $activeCourseIds = $activeCourseAccesses
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $recommendedCourses = Course::query()
            ->active()
            ->public()
            ->whereNotIn('id', $activeCourseIds ?: [0])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get();

        $answersCount = UserAnswer::query()->where('user_id', $userId)->count();
        $correctAnswersCount = UserAnswer::query()->where('user_id', $userId)->where('is_correct', true)->count();
        $accuracy = $answersCount > 0 ? round(($correctAnswersCount / $answersCount) * 100, 1) : 0;

        $stats = [
            'active_courses_count' => $activeCourseAccesses->count(),
            'study_sessions_count' => StudySession::query()->where('user_id', $userId)->whereNotNull('course_id')->count(),
            'answers_count' => $answersCount,
            'correct_answers_count' => $correctAnswersCount,
            'accuracy' => $accuracy,
            'simulated_exams_count' => SimulatedExam::query()->where('user_id', $userId)->whereNotNull('course_id')->count(),
            'favorites_count' => QuestionFavorite::query()->where('user_id', $userId)->count(),
            'open_tickets_count' => SupportTicket::query()->where('user_id', $userId)->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        $recentSimulatedExams = SimulatedExam::query()
            ->with('course')
            ->where('user_id', $userId)
            ->whereNotNull('course_id')
            ->latest('id')
            ->limit(5)
            ->get();

        $pendingTransactions = PaymentTransaction::query()
            ->with('course')
            ->where('user_id', $userId)
            ->whereNotNull('course_id')
            ->where('status', PaymentTransaction::STATUS_PENDING)
            ->latest('id')
            ->limit(3)
            ->get();

        $continuationSession = null;
        $lastCourseSession = null;

        if ($activeCourseIds !== []) {
            $continuationSession = StudySession::query()
                ->with(['course:id,title', 'subject:id,name', 'topic:id,name'])
                ->withCount('sessionQuestions as total_questions_count')
                ->withCount([
                    'sessionQuestions as answered_questions_count' => fn ($query) => $query->whereNotNull('answered_at'),
                ])
                ->where('user_id', $userId)
                ->whereIn('course_id', $activeCourseIds)
                ->whereHas('sessionQuestions', fn ($query) => $query->whereNull('answered_at'))
                ->latest('started_at')
                ->latest('id')
                ->first();

            if (! $continuationSession) {
                $lastCourseSession = StudySession::query()
                    ->with(['course:id,title', 'subject:id,name', 'topic:id,name'])
                    ->where('user_id', $userId)
                    ->whereIn('course_id', $activeCourseIds)
                    ->latest('started_at')
                    ->latest('id')
                    ->first();
            }
        }

        $studyContinuation = $this->studyContinuation(
            $continuationSession,
            $lastCourseSession,
            $activeCourseAccesses->first()?->course
        );
        $pendingErrorsCount = $this->pendingErrors->countForCourses($userId, $activeCourseIds);
        $reviewCourse = $activeCourseAccesses
            ->first(fn ($access) => $access->course
                && $this->pendingErrors->countForCourse($userId, (int) $access->course_id) > 0)
            ?->course;
        $todayPanel = $activeCourseIds !== []
            ? $this->dailyMissions->dashboard($userId, $activeCourseIds, $pendingErrorsCount)
            : null;
        $studyPlanToday = $activeCourseIds !== [] ? $this->studyPlans->today($userId) : null;
        $trialLifecycle = $this->trialLifecycle->primaryForUser($userId);
        $adaptiveRecommendation = $this->adaptiveStudy->recommendation($userId, $activeCourseIds);
        $revenueNotice = $this->revenueProtection->notices($userId)->first();

        return view('student.dashboard.index', [
            'activeCourseAccesses' => $activeCourseAccesses,
            'recommendedCourses' => $recommendedCourses,
            'stats' => $stats,
            'recentSimulatedExams' => $recentSimulatedExams,
            'pendingTransactions' => $pendingTransactions,
            'needsEmailVerification' => ! $user->hasVerifiedEmail(),
            'needsCourse' => $activeCourseAccesses->isEmpty(),
            'studyContinuation' => $studyContinuation,
            'pendingErrorsCount' => $pendingErrorsCount,
            'reviewCourse' => $reviewCourse,
            'todayPanel' => $todayPanel,
            'studyPlanToday' => $studyPlanToday,
            'trialLifecycle' => $trialLifecycle,
            'adaptiveRecommendation' => $adaptiveRecommendation,
            'revenueNotice' => $revenueNotice,
        ]);
    }

    private function studyContinuation(
        ?StudySession $continuationSession,
        ?StudySession $lastCourseSession,
        ?Course $firstActiveCourse
    ): ?array {
        if ($continuationSession && $continuationSession->course) {
            return [
                'type' => 'resume',
                'title' => 'Continue de onde parou',
                'course' => $continuationSession->course->title,
                'course_id' => (int) $continuationSession->course_id,
                'context' => $this->sessionContext($continuationSession),
                'progress' => (int) $continuationSession->answered_questions_count . ' de ' . (int) $continuationSession->total_questions_count . ' questões respondidas',
                'button' => 'Continuar estudando',
                'url' => route('student.course-study.question', $continuationSession),
            ];
        }

        if ($lastCourseSession && $lastCourseSession->course) {
            return [
                'type' => 'restart',
                'title' => 'Continue seu ritmo de estudos',
                'course' => $lastCourseSession->course->title,
                'course_id' => (int) $lastCourseSession->course_id,
                'context' => $this->sessionContext($lastCourseSession),
                'progress' => 'Sua última sessão foi concluída. Prepare a próxima com o mesmo contexto.',
                'button' => 'Estudar novamente',
                'url' => route('student.courses.study', array_filter([
                    'course' => $lastCourseSession->course_id,
                    'subject_id' => $lastCourseSession->subject_id,
                    'topic_id' => $lastCourseSession->topic_id,
                    'source_material_id' => $lastCourseSession->source_material_id,
                ])),
            ];
        }

        if ($firstActiveCourse) {
            return [
                'type' => 'start',
                'title' => 'Comece sua primeira sessão',
                'course' => $firstActiveCourse->title,
                'course_id' => (int) $firstActiveCourse->id,
                'context' => 'Escolha uma disciplina e um tópico para começar.',
                'progress' => 'Seu desempenho começará a ser calculado após as primeiras respostas.',
                'button' => 'Começar a estudar',
                'url' => route('student.courses.study', $firstActiveCourse),
            ];
        }

        return null;
    }

    private function sessionContext(StudySession $session): string
    {
        return collect([
            $session->subject?->name,
            $session->topic?->name,
        ])->filter()->implode(' · ') ?: 'Sessão geral do curso';
    }
}
