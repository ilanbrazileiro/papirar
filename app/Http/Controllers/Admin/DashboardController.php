<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $openTicketsCount = class_exists(SupportTicket::class)
            ? SupportTicket::query()->where('status', 'open')->count()
            : 0;

        $inProgressTicketsCount = class_exists(SupportTicket::class)
            ? SupportTicket::query()->where('status', 'in_progress')->count()
            : 0;

        $ticketsCount = $openTicketsCount + $inProgressTicketsCount;

        $recentTickets = class_exists(SupportTicket::class)
            ? SupportTicket::query()
                ->with('user')
                ->whereIn('status', ['open', 'in_progress'])
                ->orderByRaw("CASE WHEN status = 'open' THEN 0 WHEN status = 'in_progress' THEN 1 ELSE 2 END")
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->limit(6)
                ->get()
            : collect();

        return view('admin.dashboard.index', [
            'questionsCount' => class_exists(Question::class) ? Question::query()->count() : 0,
            'examsCount' => class_exists(Exam::class) ? Exam::query()->count() : 0,
            'customersCount' => class_exists(User::class) ? User::query()->count() : 0,
            'ticketsCount' => $ticketsCount,
            'openTicketsCount' => $openTicketsCount,
            'inProgressTicketsCount' => $inProgressTicketsCount,
            'recentTickets' => $recentTickets,
        ]);
    }
}
