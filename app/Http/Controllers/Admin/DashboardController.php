<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corporation;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $corporationId = $request->integer('corporation_id');
        $subjectId = $request->integer('subject_id');

        $questionsQuery = Question::query()->where('status', 'published');

        if ($corporationId) {
            $questionsQuery->where('corporation_id', $corporationId);
        }

        if ($subjectId) {
            $questionsQuery->where('subject_id', $subjectId);
        }

        $data = [
            'totalPublishedQuestions' => (clone $questionsQuery)->count(),
            'activeCustomers' => Subscription::query()
                ->where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '>=', now())
                ->distinct('user_id')
                ->count('user_id'),
            'openTickets' => SupportTicket::query()
                ->whereIn('status', ['open', 'pending'])
                ->count(),
            'corporations' => Corporation::query()->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'filters' => [
                'corporation_id' => $corporationId,
                'subject_id' => $subjectId,
            ],
        ];

        return view('admin.dashboard.index', $data);
    }
}
