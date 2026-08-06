<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseQuestionReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'active_only' => ['nullable', 'boolean'],
            'public_only' => ['nullable', 'boolean'],
            'show_empty_subjects' => ['nullable', 'boolean'],
        ]);

        $selectedCourseId = isset($filters['course_id']) ? (int) $filters['course_id'] : null;
        $activeOnly = $request->boolean('active_only');
        $publicOnly = $request->boolean('public_only');
        $showEmptySubjects = $request->boolean('show_empty_subjects', true);

        $courseOptions = Course::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        $courses = Course::query()
            ->with(['corporation:id,name', 'exam:id,title'])
            ->when($selectedCourseId, fn ($query) => $query->whereKey($selectedCourseId))
            ->when($activeOnly, fn ($query) => $query->where('active', true))
            ->when($publicOnly, fn ($query) => $query->where('is_public', true))
            ->orderBy('title')
            ->get();

        $rows = $courses->map(function (Course $course) use ($showEmptySubjects) {
            $scope = $this->resolveCourseScope($course);
            $subjectRows = $this->buildSubjectRows($scope, $showEmptySubjects);

            return [
                'course' => $course,
                'subject_count' => $subjectRows->count(),
                'available' => $subjectRows->sum('available'),
                'published' => $subjectRows->sum('published'),
                'reviewed' => $subjectRows->sum('reviewed'),
                'draft' => $subjectRows->sum('draft'),
                'archived' => $subjectRows->sum('archived'),
                'total' => $subjectRows->sum('total'),
                'empty_subjects' => $subjectRows->where('total', 0)->count(),
                'subjects' => $subjectRows,
            ];
        })->values();

        $totals = [
            'courses' => $rows->count(),
            'available' => $rows->sum('available'),
            'published' => $rows->sum('published'),
            'reviewed' => $rows->sum('reviewed'),
            'draft' => $rows->sum('draft'),
            'archived' => $rows->sum('archived'),
            'empty_subjects' => $rows->sum('empty_subjects'),
        ];

        $courseWithMostQuestions = $rows->sortByDesc('available')->first();
        $courseWithLeastQuestions = $rows->sortBy('available')->first();

        return view('admin.reports.course-questions.index', compact(
            'courseOptions',
            'rows',
            'totals',
            'courseWithMostQuestions',
            'courseWithLeastQuestions',
            'selectedCourseId',
            'activeOnly',
            'publicOnly',
            'showEmptySubjects'
        ));
    }

    private function buildSubjectRows(array $scope, bool $showEmptySubjects): Collection
    {
        if (empty($scope['subject_ids'])) {
            return collect();
        }

        $subjects = DB::table('subjects')
            ->whereIn('id', $scope['subject_ids'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $counts = $this->questionCountsQuery($scope)
            ->groupBy('questions.subject_id')
            ->selectRaw('questions.subject_id')
            ->selectRaw('COUNT(DISTINCT questions.id) as total')
            ->selectRaw("COUNT(DISTINCT CASE WHEN questions.status = 'published' THEN questions.id END) as published")
            ->selectRaw("COUNT(DISTINCT CASE WHEN questions.status = 'reviewed' THEN questions.id END) as reviewed")
            ->selectRaw("COUNT(DISTINCT CASE WHEN questions.status = 'draft' THEN questions.id END) as draft")
            ->selectRaw("COUNT(DISTINCT CASE WHEN questions.status = 'archived' THEN questions.id END) as archived")
            ->get()
            ->keyBy('subject_id');

        return $subjects
            ->map(function ($subject) use ($counts) {
                $count = $counts->get($subject->id);
                $published = (int) ($count->published ?? 0);
                $reviewed = (int) ($count->reviewed ?? 0);

                return [
                    'id' => (int) $subject->id,
                    'name' => $subject->name,
                    'available' => $published + $reviewed,
                    'published' => $published,
                    'reviewed' => $reviewed,
                    'draft' => (int) ($count->draft ?? 0),
                    'archived' => (int) ($count->archived ?? 0),
                    'total' => (int) ($count->total ?? 0),
                ];
            })
            ->when(! $showEmptySubjects, fn (Collection $collection) => $collection->where('total', '>', 0))
            ->values();
    }

    private function questionCountsQuery(array $scope): Builder
    {
        return DB::table('questions')
            ->whereIn('questions.subject_id', $scope['subject_ids'])
            ->when(
                ! empty($scope['topic_ids']),
                fn (Builder $query) => $query->whereIn('questions.topic_id', $scope['topic_ids'])
            )
            ->when(
                ! empty($scope['source_material_ids']),
                fn (Builder $query) => $query->whereIn('questions.source_material_id', $scope['source_material_ids'])
            );
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            return [
                'subject_ids' => DB::table('exam_subjects')
                    ->where('exam_id', $course->exam_id)
                    ->where('is_active', true)
                    ->pluck('subject_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all(),

                'topic_ids' => DB::table('exam_subject_topics')
                    ->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')
                    ->where('exam_subjects.exam_id', $course->exam_id)
                    ->where('exam_subjects.is_active', true)
                    ->where('exam_subject_topics.is_active', true)
                    ->pluck('exam_subject_topics.topic_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all(),

                'source_material_ids' => DB::table('exam_subject_source_materials')
                    ->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')
                    ->where('exam_subjects.exam_id', $course->exam_id)
                    ->where('exam_subjects.is_active', true)
                    ->where('exam_subject_source_materials.is_active', true)
                    ->pluck('exam_subject_source_materials.source_material_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all(),
            ];
        }

        return [
            'subject_ids' => DB::table('course_subjects')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),

            'topic_ids' => DB::table('course_topics')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('topic_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),

            'source_material_ids' => DB::table('course_source_materials')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('source_material_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
