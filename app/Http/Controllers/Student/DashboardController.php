<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SimulatedExam;
use App\Models\StudySession;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\UserAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $currentSubscription = Subscription::query()
            ->with('plan')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'grace'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest('id')
            ->first();

        $stats = [
            'study_sessions_count' => StudySession::query()->where('user_id', $userId)->count(),
            'answers_count' => UserAnswer::query()->where('user_id', $userId)->count(),
            'correct_answers_count' => UserAnswer::query()->where('user_id', $userId)->where('is_correct', true)->count(),
            'simulated_exams_count' => SimulatedExam::query()->where('user_id', $userId)->count(),
            'open_tickets_count' => SupportTicket::query()->where('user_id', $userId)->whereIn('status', ['open', 'pending'])->count(),
        ];

        $recentSimulatedExams = SimulatedExam::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->limit(5)
            ->get();

        return view('student.dashboard.index', [
            'currentSubscription' => $currentSubscription,
            'stats' => $stats,
            'recentSimulatedExams' => $recentSimulatedExams,
        ]);
    }
}
