<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'course_id' => $request->integer('course_id') ?: null,
            'subject_id' => $request->integer('subject_id') ?: null,
            'topic_id' => $request->integer('topic_id') ?: null,
            'status' => $request->filled('status') ? $request->string('status')->toString() : null,
        ];

        $courses = Course::query()->orderBy('title')->get(['id', 'title', 'active']);
        $subjects = Subject::query()->orderBy('name')->get(['id', 'name']);

        $topics = Topic::query()
            ->when($filters['subject_id'], fn ($query) => $query->where('subject_id', $filters['subject_id']))
            ->orderBy('name')
            ->get(['id', 'subject_id', 'name']);

        $selectedCourse = $filters['course_id'] ? Course::query()->find($filters['course_id']) : null;

        $baseQuery = $this->questionBaseQuery($selectedCourse);

        if ($filters['subject_id']) {
            $baseQuery->where('questions.subject_id', $filters['subject_id']);
        }

        if ($filters['topic_id']) {
            $baseQuery->where('questions.topic_id', $filters['topic_id']);
        }

        if ($filters['status']) {
            $baseQuery->where('questions.status', $filters['status']);
        }

        $statusCounts = (clone $baseQuery)
            ->select('questions.status', DB::raw('COUNT(*) as total'))
            ->groupBy('questions.status')
            ->pluck('total', 'status')
            ->toArray();

        $totalQuestions = array_sum($statusCounts);
        $draftCount = (int) ($statusCounts['draft'] ?? 0);
        $publishedCount = (int) ($statusCounts['published'] ?? 0);
        $reviewedCount = (int) ($statusCounts['reviewed'] ?? 0);
        $archivedCount = (int) ($statusCounts['archived'] ?? 0);
        $visibleCount = $publishedCount + $reviewedCount;
        $pendingReviewCount = $publishedCount;
        $reviewProgress = $visibleCount > 0 ? round(($reviewedCount / $visibleCount) * 100, 1) : 0;

        $statusCards = [
            ['label' => 'Total', 'value' => $totalQuestions, 'class' => 'info', 'icon' => 'fas fa-database', 'hint' => 'Questões encontradas no filtro atual.'],
            ['label' => 'Rascunhos', 'value' => $draftCount, 'class' => 'secondary', 'icon' => 'fas fa-pencil-alt', 'hint' => 'Ainda não aparecem para o aluno.'],
            ['label' => 'Publicadas', 'value' => $publishedCount, 'class' => 'warning', 'icon' => 'fas fa-eye', 'hint' => 'Aparecem para o aluno, pendentes de revisão editorial.'],
            ['label' => 'Revisadas', 'value' => $reviewedCount, 'class' => 'success', 'icon' => 'fas fa-check-circle', 'hint' => 'Aparecem para o aluno e já foram validadas.'],
            ['label' => 'Arquivadas', 'value' => $archivedCount, 'class' => 'dark', 'icon' => 'fas fa-archive', 'hint' => 'Não aparecem para o aluno.'],
        ];

        $bySubject = (clone $baseQuery)
            ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->select(
                'questions.subject_id',
                DB::raw('COALESCE(subjects.name, "Sem disciplina") as subject_name'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN questions.status = 'draft' THEN 1 ELSE 0 END) as draft_total"),
                DB::raw("SUM(CASE WHEN questions.status = 'published' THEN 1 ELSE 0 END) as published_total"),
                DB::raw("SUM(CASE WHEN questions.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_total"),
                DB::raw("SUM(CASE WHEN questions.status = 'archived' THEN 1 ELSE 0 END) as archived_total")
            )
            ->groupBy('questions.subject_id', 'subjects.name')
            ->orderByDesc('total')
            ->get();

        $byTopic = (clone $baseQuery)
            ->leftJoin('topics', 'topics.id', '=', 'questions.topic_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->select(
                'questions.topic_id',
                DB::raw('COALESCE(topics.name, "Sem tópico") as topic_name'),
                DB::raw('COALESCE(subjects.name, "Sem disciplina") as subject_name'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN questions.status = 'draft' THEN 1 ELSE 0 END) as draft_total"),
                DB::raw("SUM(CASE WHEN questions.status = 'published' THEN 1 ELSE 0 END) as published_total"),
                DB::raw("SUM(CASE WHEN questions.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_total"),
                DB::raw("SUM(CASE WHEN questions.status = 'archived' THEN 1 ELSE 0 END) as archived_total")
            )
            ->groupBy('questions.topic_id', 'topics.name', 'subjects.name')
            ->orderByDesc('total')
            ->limit(100)
            ->get();

        $byDifficulty = (clone $baseQuery)
            ->select('questions.difficulty', DB::raw('COUNT(*) as total'))
            ->groupBy('questions.difficulty')
            ->orderByDesc('total')
            ->get();

        $courseRows = Course::query()->orderBy('title')->get()->map(function (Course $course) {
            $counts = (clone $this->questionBaseQuery($course))
                ->select('questions.status', DB::raw('COUNT(*) as total'))
                ->groupBy('questions.status')
                ->pluck('total', 'status')
                ->toArray();

            $published = (int) ($counts['published'] ?? 0);
            $reviewed = (int) ($counts['reviewed'] ?? 0);
            $visible = $published + $reviewed;

            return (object) [
                'id' => $course->id,
                'title' => $course->title,
                'active' => $course->active,
                'total' => array_sum($counts),
                'draft_total' => (int) ($counts['draft'] ?? 0),
                'published_total' => $published,
                'reviewed_total' => $reviewed,
                'archived_total' => (int) ($counts['archived'] ?? 0),
                'visible_total' => $visible,
                'review_progress' => $visible > 0 ? round(($reviewed / $visible) * 100, 1) : 0,
            ];
        });

        return view('admin.reports.questions.index', compact(
            'filters',
            'courses',
            'subjects',
            'topics',
            'selectedCourse',
            'statusCards',
            'totalQuestions',
            'visibleCount',
            'pendingReviewCount',
            'reviewedCount',
            'reviewProgress',
            'bySubject',
            'byTopic',
            'byDifficulty',
            'courseRows'
        ));
    }

    private function questionBaseQuery(?Course $course = null): Builder
    {
        $query = DB::table('questions');

        if (! $course) {
            return $query;
        }

        if (! empty($course->corporation_id)) {
            $query->where('questions.corporation_id', $course->corporation_id);
        }

        if (! empty($course->exam_id)) {
            $query->where('questions.exam_id', $course->exam_id);
        }

        $subjectIds = DB::table('course_subjects')
            ->where('course_id', $course->id)
            ->pluck('subject_id')
            ->filter()
            ->values();

        if ($subjectIds->isNotEmpty()) {
            $query->whereIn('questions.subject_id', $subjectIds);
        }

        $topicIds = DB::table('course_topics')
            ->where('course_id', $course->id)
            ->pluck('topic_id')
            ->filter()
            ->values();

        if ($topicIds->isNotEmpty()) {
            $query->whereIn('questions.topic_id', $topicIds);
        }

        return $query;
    }
}
