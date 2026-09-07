<?php

namespace App\Services\Study;

use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\CourseAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudyPlanService
{
    public function activeForUser(int $userId): ?StudyPlan
    {
        if (! Schema::hasTable('study_plans')) return null;

        return StudyPlan::query()
            ->with('course:id,title')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereHas('course.accesses', fn ($query) => $query
                ->where('user_id', $userId)
                ->where('status', CourseAccess::STATUS_ACTIVE)
                ->where(fn ($access) => $access->whereNull('ends_at')->orWhere('ends_at', '>=', now())))
            ->latest('updated_at')
            ->first();
    }

    public function today(int $userId): ?array
    {
        $plan = $this->activeForUser($userId);
        if (! $plan || ! $plan->course) return null;

        $weekdays = collect($plan->weekdays)->map(fn ($day) => (int) $day)->all();
        $today = today();
        $isStudyDay = in_array($today->dayOfWeekIso, $weekdays, true);
        $subject = $isStudyDay ? $this->subjectForDate($plan, $today) : null;
        $answered = $subject ? $this->answeredOnDate($plan, (int) $subject->id, $today) : 0;
        $target = (int) $plan->daily_question_target;
        $next = $this->nextStudy($plan, $isStudyDay ? $today->copy()->addDay() : $today);

        return [
            'plan' => $plan,
            'is_study_day' => $isStudyDay,
            'subject' => $subject,
            'answered' => $answered,
            'target' => $target,
            'remaining' => max(0, $target - $answered),
            'percent' => min(100, (int) round(($answered / max(1, $target)) * 100)),
            'completed' => $answered >= $target,
            'next' => $next,
        ];
    }

    public function availableSubjects(int $courseId, bool $inheritExamScope, ?int $examId)
    {
        $ids = $inheritExamScope && $examId
            ? DB::table('exam_subjects')->where('exam_id', $examId)->where('is_active', true)->orderBy('sort_order')->pluck('subject_id')
            : DB::table('course_subjects')->where('course_id', $courseId)->where('is_active', true)->orderBy('sort_order')->pluck('subject_id');

        return Subject::query()->whereIn('id', $ids)->get()->sortBy(fn ($subject) => array_search($subject->id, $ids->all()))->values();
    }

    private function subjectForDate(StudyPlan $plan, Carbon $date): ?Subject
    {
        $ids = collect($plan->subject_ids)->map(fn ($id) => (int) $id)->filter()->values();
        if ($ids->isEmpty()) return null;

        $position = $this->studyDayPosition($plan, $date) % $ids->count();
        return Subject::query()->find($ids[$position]);
    }

    private function studyDayPosition(StudyPlan $plan, Carbon $date): int
    {
        $cursor = Carbon::parse($plan->starts_on)->startOfDay();
        $weekdays = collect($plan->weekdays)->map(fn ($day) => (int) $day)->all();
        $position = 0;
        while ($cursor->lt($date) && $position < 5000) {
            if (in_array($cursor->dayOfWeekIso, $weekdays, true)) $position++;
            $cursor->addDay();
        }
        return $position;
    }

    private function answeredOnDate(StudyPlan $plan, int $subjectId, Carbon $date): int
    {
        return DB::table('user_answers as ua')
            ->join('study_sessions as ss', 'ss.id', '=', 'ua.study_session_id')
            ->join('questions as q', 'q.id', '=', 'ua.question_id')
            ->where('ua.user_id', $plan->user_id)->where('ss.course_id', $plan->course_id)
            ->where('q.subject_id', $subjectId)->whereDate('ua.answered_at', $date->toDateString())->count();
    }

    private function nextStudy(StudyPlan $plan, Carbon $from): ?array
    {
        $weekdays = collect($plan->weekdays)->map(fn ($day) => (int) $day)->all();
        for ($offset = 0; $offset < 8; $offset++) {
            $date = $from->copy()->addDays($offset);
            if (in_array($date->dayOfWeekIso, $weekdays, true)) {
                return ['date' => $date, 'subject' => $this->subjectForDate($plan, $date)];
            }
        }
        return null;
    }
}
