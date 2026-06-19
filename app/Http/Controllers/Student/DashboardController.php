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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
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

        return view('student.dashboard.index', [
            'activeCourseAccesses' => $activeCourseAccesses,
            'recommendedCourses' => $recommendedCourses,
            'stats' => $stats,
            'recentSimulatedExams' => $recentSimulatedExams,
            'pendingTransactions' => $pendingTransactions,
            'needsEmailVerification' => ! $user->hasVerifiedEmail(),
            'needsCourse' => $activeCourseAccesses->isEmpty(),
        ]);
    }
}
