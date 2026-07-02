<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ContentDashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'questions_total' => $this->countTable('questions'),
            'questions_published' => $this->countWhere('questions', 'status', 'published'),
            'questions_reviewed' => $this->countWhere('questions', 'status', 'reviewed'),
            'questions_draft' => $this->countWhere('questions', 'status', 'draft'),
            'courses_total' => $this->countTable('courses'),
            'courses_active' => $this->countWhere('courses', 'active', true),
            'courses_public' => $this->countWhere('courses', 'is_public', true),
        ];

        $products = $this->productCards();

        return view('admin.content-dashboard', compact('stats', 'products'));
    }

    private function productCards(): Collection
    {
        if (!Schema::hasTable('courses')) {
            return collect();
        }

        return Course::query()
            ->with(['corporation:id,name', 'exam:id,title'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (Course $course) {
                $questionQuery = $this->questionQueryForCourse($course);

                $course->questions_total = (clone $questionQuery)->count();
                $course->questions_visible = (clone $questionQuery)
                    ->whereIn('questions.status', ['published', 'reviewed'])
                    ->count();
                $course->questions_draft = (clone $questionQuery)
                    ->where('questions.status', 'draft')
                    ->count();
                $course->subjects_count = $this->courseRelationIds('course_subjects', $course->id, 'subject_id')->count();
                $course->topics_count = $this->courseRelationIds('course_topics', $course->id, 'topic_id')->count();
                $course->active_accesses_count = $this->activeAccessCount($course->id);

                return $course;
            });
    }

    private function questionQueryForCourse(Course $course): Builder
    {
        $query = DB::table('questions');

        if (!empty($course->corporation_id) && Schema::hasColumn('questions', 'corporation_id')) {
            $query->where(function ($q) use ($course) {
                $q->whereNull('questions.corporation_id')
                    ->orWhere('questions.corporation_id', $course->corporation_id);
            });
        }

        if (!empty($course->exam_id) && Schema::hasColumn('questions', 'exam_id')) {
            $query->where(function ($q) use ($course) {
                $q->whereNull('questions.exam_id')
                    ->orWhere('questions.exam_id', $course->exam_id);
            });
        }

        $subjectIds = $this->courseRelationIds('course_subjects', $course->id, 'subject_id');
        if ($subjectIds->isNotEmpty() && Schema::hasColumn('questions', 'subject_id')) {
            $query->whereIn('questions.subject_id', $subjectIds);
        }

        $topicIds = $this->courseRelationIds('course_topics', $course->id, 'topic_id');
        if ($topicIds->isNotEmpty() && Schema::hasColumn('questions', 'topic_id')) {
            $query->whereIn('questions.topic_id', $topicIds);
        }

        return $query;
    }

    private function courseRelationIds(string $table, int $courseId, string $column): Collection
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return collect();
        }

        $query = DB::table($table)->where('course_id', $courseId);
        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->pluck($column)->filter()->unique()->values();
    }

    private function activeAccessCount(int $courseId): int
    {
        if (!Schema::hasTable('course_accesses')) {
            return 0;
        }

        $query = DB::table('course_accesses')->where('course_id', $courseId);

        if (Schema::hasColumn('course_accesses', 'status')) {
            $query->where('status', 'active');
        }
        if (Schema::hasColumn('course_accesses', 'starts_at')) {
            $query->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()));
        }
        if (Schema::hasColumn('course_accesses', 'ends_at')) {
            $query->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
        }

        return (int) $query->count();
    }

    private function countTable(string $table): int
    {
        return Schema::hasTable($table) ? (int) DB::table($table)->count() : 0;
    }

    private function countWhere(string $table, string $column, mixed $value): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)->where($column, $value)->count();
    }
}
