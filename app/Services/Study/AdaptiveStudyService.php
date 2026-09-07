<?php

namespace App\Services\Study;

use App\Models\Course;
use App\Models\Question;
use App\Models\StudySession;
use Illuminate\Support\Facades\DB;

class AdaptiveStudyService
{
    public function recommendation(int $userId, array $activeCourseIds): ?array
    {
        if ($activeCourseIds === []) return null;

        $row = DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->join('questions as q', 'q.id', '=', 'ua.question_id')
            ->join('subjects as s', 's.id', '=', 'q.subject_id')
            ->join('topics as t', 't.id', '=', 'q.topic_id')
            ->where('ua.user_id', $userId)
            ->whereIn('ss.course_id', $activeCourseIds)
            ->whereIn('q.status', Question::STUDENT_VISIBLE_STATUSES)
            ->whereNotExists(function ($query) use ($userId) {
                $query->selectRaw('1')->from('user_answers as newer')
                    ->join('study_sessions as newer_ss', 'newer_ss.id', '=', 'newer.study_session_id')
                    ->whereColumn('newer.question_id', 'ua.question_id')
                    ->whereColumn('newer_ss.course_id', 'ss.course_id')
                    ->where('newer.user_id', $userId)
                    ->where(fn ($later) => $later->whereColumn('newer.answered_at', '>', 'ua.answered_at')
                        ->orWhere(fn ($same) => $same->whereColumn('newer.answered_at', '=', 'ua.answered_at')->whereColumn('newer.id', '>', 'ua.id')));
            })
            ->groupBy('ss.course_id', 'q.subject_id', 's.name', 'q.topic_id', 't.name')
            ->selectRaw('ss.course_id, q.subject_id, s.name as subject_name, q.topic_id, t.name as topic_name, COUNT(*) as answered, SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) as correct, SUM(CASE WHEN ua.is_correct = 0 THEN 1 ELSE 0 END) as wrong')
            ->havingRaw('COUNT(*) >= 5')
            ->havingRaw('(SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) < 0.70')
            ->orderByRaw('(SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) ASC')
            ->orderByDesc('wrong')
            ->first();

        if (! $row) return null;

        $course = Course::query()->find($row->course_id);
        if (! $course) return null;

        $answered = (int) $row->answered;
        $correct = (int) $row->correct;
        $wrong = (int) $row->wrong;

        return [
            'course' => $course,
            'subject_id' => (int) $row->subject_id,
            'subject' => $row->subject_name,
            'topic_id' => (int) $row->topic_id,
            'topic' => $row->topic_name,
            'answered' => $answered,
            'wrong' => $wrong,
            'accuracy' => round(($correct / max(1, $answered)) * 100, 1),
            'quantity' => max(1, min(10, $wrong)),
            'mode' => 'review',
        ];
    }

    public function outcome(StudySession $session, int $userId, int $currentCorrect, int $currentTotal): ?array
    {
        if (! $session->course_id || ! $session->topic_id || $currentTotal === 0) return null;

        $previous = DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->join('questions as q', 'q.id', '=', 'ua.question_id')
            ->where('ua.user_id', $userId)->where('ss.course_id', $session->course_id)
            ->where('q.topic_id', $session->topic_id)
            ->where('ua.answered_at', '<', $session->started_at)
            ->whereNotExists(function ($query) use ($userId, $session) {
                $query->selectRaw('1')->from('user_answers as newer')
                    ->join('study_sessions as newer_ss', 'newer_ss.id', '=', 'newer.study_session_id')
                    ->whereColumn('newer.question_id', 'ua.question_id')
                    ->where('newer.user_id', $userId)->where('newer_ss.course_id', $session->course_id)
                    ->where('newer.answered_at', '<', $session->started_at)
                    ->where(fn ($later) => $later->whereColumn('newer.answered_at', '>', 'ua.answered_at')
                        ->orWhere(fn ($same) => $same->whereColumn('newer.answered_at', '=', 'ua.answered_at')->whereColumn('newer.id', '>', 'ua.id')));
            })
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN ua.is_correct = 1 THEN 1 ELSE 0 END) as correct')->first();

        $previousTotal = (int) ($previous->total ?? 0);
        if ($previousTotal < 5) return null;

        $previousAccuracy = round(((int) $previous->correct / $previousTotal) * 100, 1);
        $currentAccuracy = round(($currentCorrect / $currentTotal) * 100, 1);

        return [
            'previous_accuracy' => $previousAccuracy,
            'current_accuracy' => $currentAccuracy,
            'improved' => $currentAccuracy > $previousAccuracy,
        ];
    }
}
