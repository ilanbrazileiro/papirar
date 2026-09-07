<?php

namespace App\Services\Study;

use App\Models\DailyMission;
use App\Models\UserAnswer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyMissionService
{
    private const DEFINITIONS = [
        'answer_5' => ['title' => 'Aquecimento', 'description' => 'Responda 5 questões.', 'target' => 5],
        'answer_10' => ['title' => 'Meta diária', 'description' => 'Responda 10 questões.', 'target' => 10],
        'complete_session' => ['title' => 'Sessão completa', 'description' => 'Conclua uma sessão de estudo.', 'target' => 1],
        'correct_streak_3' => ['title' => 'Boa sequência', 'description' => 'Acerte 3 questões consecutivas.', 'target' => 3],
        'accuracy_70' => ['title' => 'Precisão', 'description' => 'Alcance 70% em pelo menos 10 questões.', 'target' => 10],
        'correct_review_3' => ['title' => 'Corrija seus erros', 'description' => 'Acerte 3 questões durante revisões.', 'target' => 3],
        'review_5' => ['title' => 'Hora da revisão', 'description' => 'Revise 5 questões erradas.', 'target' => 5],
        'two_subjects' => ['title' => 'Variedade de estudo', 'description' => 'Responda questões de 2 disciplinas.', 'target' => 2],
    ];

    public function dashboard(int $userId, array $activeCourseIds, int $pendingErrors): array
    {
        $missions = $this->missionsForToday($userId, $pendingErrors);
        $metrics = $this->todayMetrics($userId, $activeCourseIds);
        $items = $missions->map(function (DailyMission $mission) use ($metrics) {
            $definition = self::DEFINITIONS[$mission->code];
            [$value, $progressText, $completed] = $this->progress($mission->code, $mission->target, $metrics);

            if ($completed && ! $mission->completed_at) {
                $mission->forceFill(['completed_at' => now()])->save();
            }

            $percent = $mission->code === 'accuracy_70'
                ? min(100, (int) round(min(
                    $metrics['answers'] / 10,
                    ($metrics['answers'] > 0 ? $metrics['correct'] / $metrics['answers'] : 0) / .70
                ) * 100))
                : min(100, (int) round(($value / max(1, $mission->target)) * 100));

            return [
                'code' => $mission->code,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'value' => $value,
                'target' => $mission->target,
                'progress_text' => $progressText,
                'percent' => $completed ? 100 : $percent,
                'completed' => $completed,
                'completed_now' => $completed && $mission->wasChanged('completed_at'),
            ];
        })->values();

        return [
            'missions' => $items,
            'completed' => $items->where('completed', true)->count(),
            'streak' => $this->studyStreak($userId),
        ];
    }

    private function missionsForToday(int $userId, int $pendingErrors): Collection
    {
        $today = today()->toDateString();
        $existing = DailyMission::query()->where('user_id', $userId)->whereDate('mission_date', $today)->orderBy('id')->get();
        if ($existing->count() === 3) {
            return $existing;
        }

        $seed = abs(crc32($userId . '|' . $today));
        $codes = [
            ['answer_5', 'answer_10'][$seed % 2],
            ['complete_session', 'correct_streak_3', 'accuracy_70'][($seed >> 2) % 3],
            $pendingErrors > 0
                ? ['correct_review_3', 'review_5'][($seed >> 4) % 2]
                : 'two_subjects',
        ];

        foreach ($codes as $code) {
            DailyMission::query()->firstOrCreate(
                ['user_id' => $userId, 'mission_date' => $today, 'code' => $code],
                ['target' => self::DEFINITIONS[$code]['target']]
            );
        }

        return DailyMission::query()->where('user_id', $userId)->whereDate('mission_date', $today)->orderBy('id')->limit(3)->get();
    }

    private function todayMetrics(int $userId, array $courseIds): array
    {
        $start = today()->startOfDay();
        $end = today()->endOfDay();
        $answers = UserAnswer::query()
            ->where('user_id', $userId)
            ->whereBetween('answered_at', [$start, $end])
            ->orderBy('answered_at')->orderBy('id')->get(['id', 'study_session_id', 'question_id', 'is_correct']);

        $streak = 0;
        $bestStreak = 0;
        foreach ($answers as $answer) {
            $streak = $answer->is_correct ? $streak + 1 : 0;
            $bestStreak = max($bestStreak, $streak);
        }

        $reviewQuery = DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->where('ua.user_id', $userId)->where('ss.mode', 'review')
            ->whereBetween('ua.answered_at', [$start, $end])
            ->when($courseIds !== [], fn ($q) => $q->whereIn('ss.course_id', $courseIds));

        return [
            'answers' => $answers->count(),
            'correct' => $answers->where('is_correct', true)->count(),
            'correct_streak' => $bestStreak,
            'completed_sessions' => DB::table('study_sessions')->where('user_id', $userId)->whereBetween('finished_at', [$start, $end])->when($courseIds !== [], fn ($q) => $q->whereIn('course_id', $courseIds))->count(),
            'reviewed' => (clone $reviewQuery)->count(),
            'corrected_reviews' => (clone $reviewQuery)->where('ua.is_correct', true)->count(),
            'subjects' => DB::table('user_answers as ua')->join('questions as q', 'q.id', '=', 'ua.question_id')->where('ua.user_id', $userId)->whereBetween('ua.answered_at', [$start, $end])->distinct()->count('q.subject_id'),
        ];
    }

    private function progress(string $code, int $target, array $metrics): array
    {
        return match ($code) {
            'answer_5', 'answer_10' => [$metrics['answers'], "{$metrics['answers']}/{$target} respondidas", $metrics['answers'] >= $target],
            'complete_session' => [$metrics['completed_sessions'], "{$metrics['completed_sessions']}/1 concluída", $metrics['completed_sessions'] >= 1],
            'correct_streak_3' => [$metrics['correct_streak'], "{$metrics['correct_streak']}/3 consecutivas", $metrics['correct_streak'] >= 3],
            'accuracy_70' => [$metrics['answers'], $metrics['answers'] . '/10 respostas · ' . ($metrics['answers'] ? round(($metrics['correct'] / $metrics['answers']) * 100) : 0) . '% de acertos', $metrics['answers'] >= 10 && ($metrics['correct'] / $metrics['answers']) >= .70],
            'correct_review_3' => [$metrics['corrected_reviews'], "{$metrics['corrected_reviews']}/3 erros superados", $metrics['corrected_reviews'] >= 3],
            'review_5' => [$metrics['reviewed'], "{$metrics['reviewed']}/5 revisadas", $metrics['reviewed'] >= 5],
            'two_subjects' => [$metrics['subjects'], "{$metrics['subjects']}/2 disciplinas", $metrics['subjects'] >= 2],
        };
    }

    private function studyStreak(int $userId): array
    {
        $days = DB::table('user_answers')->where('user_id', $userId)->selectRaw('DATE(answered_at) as study_day')->distinct()->orderBy('study_day')->pluck('study_day')->map(fn ($day) => Carbon::parse($day)->startOfDay());
        $best = 0;
        $run = 0;
        $previous = null;
        foreach ($days as $day) {
            $run = $previous && $previous->copy()->addDay()->equalTo($day) ? $run + 1 : 1;
            $best = max($best, $run);
            $previous = $day;
        }

        $set = $days->map->toDateString()->flip();
        $cursor = today();
        if (! $set->has($cursor->toDateString())) {
            $cursor->subDay();
        }
        $current = 0;
        while ($set->has($cursor->toDateString())) {
            $current++;
            $cursor->subDay();
        }

        return ['current' => $current, 'best' => $best, 'total_days' => $days->count()];
    }
}
