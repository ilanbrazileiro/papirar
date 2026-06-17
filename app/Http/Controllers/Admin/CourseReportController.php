<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : now()->subDays(30)->startOfDay();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();
        $courseId = $request->integer('course_id') ?: null;

        $courses = Course::query()
            ->orderBy('title')
            ->get(['id', 'title', 'active', 'is_public', 'price', 'quarterly_price', 'semiannual_price']);

        $courseRows = $courses
            ->when($courseId, fn ($collection) => $collection->where('id', $courseId))
            ->map(function (Course $course) use ($dateFrom, $dateTo) {
                return $this->buildCourseRow($course, $dateFrom, $dateTo);
            })
            ->values();

        $totals = [
            'courses' => $courseRows->count(),
            'active_accesses' => $courseRows->sum('active_accesses'),
            'paid_accesses' => $courseRows->sum('paid_accesses'),
            'trial_accesses' => $courseRows->sum('trial_accesses'),
            'pending_payments' => $courseRows->sum('pending_payments'),
            'paid_payments' => $courseRows->sum('paid_payments'),
            'revenue' => $courseRows->sum('revenue'),
            'answers' => $courseRows->sum('answers'),
            'simulated_finished' => $courseRows->sum('simulated_finished'),
        ];

        $topRevenueCourses = $courseRows->sortByDesc('revenue')->take(5)->values();
        $topEngagementCourses = $courseRows->sortByDesc('answers')->take(5)->values();

        return view('admin.reports.courses.index', [
            'courses' => $courses,
            'courseRows' => $courseRows,
            'totals' => $totals,
            'topRevenueCourses' => $topRevenueCourses,
            'topEngagementCourses' => $topEngagementCourses,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'courseId' => $courseId,
        ]);
    }

    public function show(Request $request, Course $course)
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : now()->subDays(30)->startOfDay();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        $row = $this->buildCourseRow($course, $dateFrom, $dateTo);

        $payments = DB::table('payment_transactions')
            ->leftJoin('users', 'payment_transactions.user_id', '=', 'users.id')
            ->leftJoin('subscriptions', 'payment_transactions.subscription_id', '=', 'subscriptions.id')
            ->where('payment_transactions.course_id', $course->id)
            ->whereBetween('payment_transactions.created_at', [$dateFrom, $dateTo])
            ->orderByDesc('payment_transactions.id')
            ->select([
                'payment_transactions.id',
                'payment_transactions.status',
                'payment_transactions.amount',
                'payment_transactions.gateway',
                'payment_transactions.provider_payment_id',
                'payment_transactions.paid_at',
                'payment_transactions.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'subscriptions.billing_cycle',
                'subscriptions.period_days',
            ])
            ->limit(100)
            ->get();

        $accesses = DB::table('course_accesses')
            ->leftJoin('users', 'course_accesses.user_id', '=', 'users.id')
            ->where('course_accesses.course_id', $course->id)
            ->orderByDesc('course_accesses.id')
            ->select([
                'course_accesses.id',
                'course_accesses.status',
                'course_accesses.access_type',
                'course_accesses.starts_at',
                'course_accesses.ends_at',
                'course_accesses.bonus_days',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->limit(100)
            ->get();

        $subjects = $this->subjectPerformance($course, $dateFrom, $dateTo);
        $recentAnswers = $this->recentAnswers($course, $dateFrom, $dateTo);

        return view('admin.reports.courses.show', [
            'course' => $course,
            'row' => $row,
            'payments' => $payments,
            'accesses' => $accesses,
            'subjects' => $subjects,
            'recentAnswers' => $recentAnswers,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    private function buildCourseRow(Course $course, Carbon $dateFrom, Carbon $dateTo): array
    {
        $activeAccesses = DB::table('course_accesses')
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->count();

        $paidAccesses = DB::table('course_accesses')
            ->where('course_id', $course->id)
            ->where('access_type', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $trialAccesses = DB::table('course_accesses')
            ->where('course_id', $course->id)
            ->where('access_type', 'trial')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $manualAccesses = DB::table('course_accesses')
            ->where('course_id', $course->id)
            ->whereIn('access_type', ['manual', 'bonus'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $pendingPayments = DB::table('payment_transactions')
            ->where('course_id', $course->id)
            ->where('status', 'pending')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $paidPayments = DB::table('payment_transactions')
            ->where('course_id', $course->id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $revenue = (float) DB::table('payment_transactions')
            ->where('course_id', $course->id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('amount');

        $studySessions = 0;
        $answers = 0;
        $correctAnswers = 0;

        if (Schema::hasColumn('study_sessions', 'course_id')) {
            $studySessions = DB::table('study_sessions')
                ->where('course_id', $course->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count();

            $answerStats = DB::table('user_answers')
                ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
                ->where('study_sessions.course_id', $course->id)
                ->whereBetween('user_answers.answered_at', [$dateFrom, $dateTo])
                ->selectRaw('COUNT(*) as answered, SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
                ->first();

            $answers = (int) ($answerStats->answered ?? 0);
            $correctAnswers = (int) ($answerStats->correct ?? 0);
        }

        $simulatedTotal = 0;
        $simulatedFinished = 0;
        $simulatedAvg = 0.0;

        if (Schema::hasColumn('simulated_exams', 'course_id')) {
            $simulated = DB::table('simulated_exams')
                ->where('course_id', $course->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN finished_at IS NOT NULL THEN 1 ELSE 0 END) as finished, AVG(CASE WHEN finished_at IS NOT NULL THEN accuracy ELSE NULL END) as avg_accuracy')
                ->first();

            $simulatedTotal = (int) ($simulated->total ?? 0);
            $simulatedFinished = (int) ($simulated->finished ?? 0);
            $simulatedAvg = (float) ($simulated->avg_accuracy ?? 0);
        }

        $accuracy = $answers > 0 ? round(($correctAnswers / $answers) * 100, 1) : 0.0;
        $conversionRate = ($paidAccesses + $trialAccesses) > 0 ? round(($paidAccesses / max(1, $paidAccesses + $trialAccesses)) * 100, 1) : 0.0;

        return [
            'course' => $course,
            'active_accesses' => $activeAccesses,
            'paid_accesses' => $paidAccesses,
            'trial_accesses' => $trialAccesses,
            'manual_accesses' => $manualAccesses,
            'pending_payments' => $pendingPayments,
            'paid_payments' => $paidPayments,
            'revenue' => $revenue,
            'study_sessions' => $studySessions,
            'answers' => $answers,
            'correct_answers' => $correctAnswers,
            'accuracy' => $accuracy,
            'simulated_total' => $simulatedTotal,
            'simulated_finished' => $simulatedFinished,
            'simulated_avg' => $simulatedAvg,
            'conversion_rate' => $conversionRate,
        ];
    }

    private function subjectPerformance(Course $course, Carbon $dateFrom, Carbon $dateTo)
    {
        if (! Schema::hasColumn('study_sessions', 'course_id')) {
            return collect();
        }

        return DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->leftJoin('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('study_sessions.course_id', $course->id)
            ->whereBetween('user_answers.answered_at', [$dateFrom, $dateTo])
            ->groupBy('subjects.id', 'subjects.name')
            ->selectRaw('subjects.name as subject_name, COUNT(*) as answered, SUM(CASE WHEN user_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct')
            ->orderByDesc('answered')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $row->accuracy = $row->answered > 0 ? round(($row->correct / $row->answered) * 100, 1) : 0;
                return $row;
            });
    }

    private function recentAnswers(Course $course, Carbon $dateFrom, Carbon $dateTo)
    {
        if (! Schema::hasColumn('study_sessions', 'course_id')) {
            return collect();
        }

        return DB::table('user_answers')
            ->join('study_sessions', 'user_answers.study_session_id', '=', 'study_sessions.id')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->leftJoin('users', 'user_answers.user_id', '=', 'users.id')
            ->where('study_sessions.course_id', $course->id)
            ->whereBetween('user_answers.answered_at', [$dateFrom, $dateTo])
            ->orderByDesc('user_answers.answered_at')
            ->select([
                'user_answers.id',
                'user_answers.is_correct',
                'user_answers.answered_at',
                'questions.id as question_id',
                'questions.statement',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->limit(30)
            ->get();
    }
}
