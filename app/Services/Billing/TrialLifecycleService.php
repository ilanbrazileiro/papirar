<?php

namespace App\Services\Billing;

use App\Models\CourseAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrialLifecycleService
{
    public function forUser(int $userId): Collection
    {
        $trials = CourseAccess::query()
            ->with('course')
            ->where('user_id', $userId)
            ->where('access_type', CourseAccess::TYPE_TRIAL)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', now()->subDays(30))
            ->latest('ends_at')
            ->get();

        $paidActiveCourseIds = CourseAccess::query()
            ->where('user_id', $userId)
            ->where('access_type', '!=', CourseAccess::TYPE_TRIAL)
            ->where('status', CourseAccess::STATUS_ACTIVE)
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->pluck('course_id')->map(fn ($id) => (int) $id);

        return $trials
            ->reject(fn (CourseAccess $trial) => ! $trial->course || $paidActiveCourseIds->contains((int) $trial->course_id))
            ->unique('course_id')
            ->map(fn (CourseAccess $trial) => $this->build($trial, $userId))
            ->sortBy('priority')
            ->values();
    }

    public function primaryForUser(int $userId): ?array
    {
        return $this->forUser($userId)->first();
    }

    private function build(CourseAccess $trial, int $userId): array
    {
        $metrics = $this->metrics($trial, $userId);
        $expired = $trial->ends_at->isPast();
        $daysRemaining = $expired ? 0 : now()->startOfDay()->diffInDays($trial->ends_at->copy()->startOfDay()) + 1;

        if ($expired) {
            $stage = 'expired';
            $priority = 1;
            $eyebrow = 'Seu teste terminou';
            $title = 'Não perca o progresso que você já construiu';
        } elseif ($daysRemaining <= 1) {
            $stage = 'last_day';
            $priority = 0;
            $eyebrow = 'Último dia do teste';
            $title = 'Continue sua preparação sem interrupção';
        } elseif ($daysRemaining <= 3) {
            $stage = 'ending_soon';
            $priority = 2;
            $eyebrow = "Seu teste termina em {$daysRemaining} dias";
            $title = 'Mantenha acesso a tudo que está usando';
        } elseif ($metrics['answers'] === 0) {
            $stage = 'not_started';
            $priority = 3;
            $eyebrow = "Você ainda tem {$daysRemaining} dias grátis";
            $title = 'Comece agora e aproveite seu período de teste';
        } else {
            $stage = 'active';
            $priority = 4;
            $eyebrow = "{$daysRemaining} dias restantes no teste";
            $title = 'Seu progresso já começou';
        }

        return compact('trial', 'stage', 'priority', 'eyebrow', 'title', 'daysRemaining', 'metrics') + [
            'course' => $trial->course,
            'can_study' => ! $expired,
        ];
    }

    private function metrics(CourseAccess $trial, int $userId): array
    {
        $answers = DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->join('questions as q', 'q.id', '=', 'ua.question_id')
            ->where('ua.user_id', $userId)
            ->where('ss.course_id', $trial->course_id)
            ->whereBetween('ua.answered_at', [$trial->starts_at ?: $trial->created_at, $trial->ends_at])
            ->selectRaw('COUNT(*) as answers, SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) as correct, COUNT(DISTINCT q.subject_id) as subjects')
            ->first();

        $count = (int) ($answers->answers ?? 0);

        return [
            'answers' => $count,
            'correct' => (int) ($answers->correct ?? 0),
            'subjects' => (int) ($answers->subjects ?? 0),
            'accuracy' => $count > 0 ? round(((int) $answers->correct / $count) * 100, 1) : 0,
        ];
    }
}
