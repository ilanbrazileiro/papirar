<?php

namespace App\Services\Study;

use Illuminate\Support\Facades\DB;

class PendingErrorReviewService
{
    /** @return array<int, int> */
    public function questionIds(int $userId, int $courseId): array
    {
        return $this->baseQuery($userId, [$courseId])
            ->distinct()
            ->pluck('ua.question_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function countForCourse(int $userId, int $courseId): int
    {
        return $this->baseQuery($userId, [$courseId])
            ->distinct()
            ->count('ua.question_id');
    }

    public function countForCourses(int $userId, array $courseIds): int
    {
        if ($courseIds === []) {
            return 0;
        }

        return $this->baseQuery($userId, $courseIds)
            ->distinct()
            ->count(DB::raw("CONCAT(ss.course_id, ':', ua.question_id)"));
    }

    private function baseQuery(int $userId, array $courseIds)
    {
        return DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->where('ua.user_id', $userId)
            ->whereIn('ss.course_id', $courseIds)
            ->where('ua.is_correct', false)
            ->whereNotExists(function ($query) use ($userId) {
                $query->selectRaw('1')
                    ->from('user_answers as newer')
                    ->join('study_sessions as newer_ss', 'newer_ss.id', '=', 'newer.study_session_id')
                    ->whereColumn('newer.question_id', 'ua.question_id')
                    ->whereColumn('newer_ss.course_id', 'ss.course_id')
                    ->where('newer.user_id', $userId)
                    ->where(function ($later) {
                        $later->whereColumn('newer.answered_at', '>', 'ua.answered_at')
                            ->orWhere(function ($sameTime) {
                                $sameTime->whereColumn('newer.answered_at', '=', 'ua.answered_at')
                                    ->whereColumn('newer.id', '>', 'ua.id');
                            });
                    });
            });
    }
}
