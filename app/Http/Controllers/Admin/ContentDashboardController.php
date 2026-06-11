<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ContentDashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'questions_total' => $this->countTable('questions'),
            'questions_published' => $this->countWhere('questions', 'status', 'published'),
            'questions_draft' => $this->countWhere('questions', 'status', 'draft'),
        ];

        $plannedExams = $this->plannedExamCards();

        return view('admin.content-dashboard', compact('stats', 'plannedExams'));
    }

    private function plannedExamCards(): Collection
    {
        if (! $this->tableExists('exams')) {
            return collect();
        }

        $query = DB::table('exams')
            ->select([
                'exams.id',
                'exams.title',
                'exams.year',
                'exams.exam_type',
                'exams.status',
                'exams.corporation_id',
            ])
            ->where('exams.status', 'planned')
            ->orderByDesc('exams.year')
            ->orderBy('exams.title');

        if ($this->tableExists('corporations') && $this->columnExists('exams', 'corporation_id')) {
            $query->leftJoin('corporations', 'corporations.id', '=', 'exams.corporation_id')
                ->addSelect('corporations.name as corporation_name');
        }

        return $query->get()->map(function ($exam) {
            $examSubjectRows = $this->examSubjectsForExam((int) $exam->id);
            $subjectIds = $examSubjectRows->pluck('subject_id')->filter()->unique()->values()->all();
            $examSubjectIds = $examSubjectRows->pluck('id')->filter()->unique()->values()->all();
            $topicIds = $this->topicIdsForExamSubjects($examSubjectIds);

            $exam->subjects_count = count($subjectIds);
            $exam->topics_count = count($topicIds);
            $exam->questions_count = $this->countAvailableQuestionsForExam($exam, $subjectIds, $topicIds);

            return $exam;
        });
    }

    private function examSubjectsForExam(int $examId): Collection
    {
        if (! $this->tableExists('exam_subjects')) {
            return collect();
        }

        $query = DB::table('exam_subjects')
            ->select(['id', 'subject_id'])
            ->where('exam_id', $examId);

        if ($this->columnExists('exam_subjects', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    private function topicIdsForExamSubjects(array $examSubjectIds): array
    {
        if (empty($examSubjectIds) || ! $this->tableExists('exam_subject_topics')) {
            return [];
        }

        $query = DB::table('exam_subject_topics')
            ->whereIn('exam_subject_id', $examSubjectIds);

        if ($this->columnExists('exam_subject_topics', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->pluck('topic_id')->filter()->unique()->values()->all();
    }

    private function countAvailableQuestionsForExam(object $exam, array $subjectIds, array $topicIds): int
    {
        if (! $this->tableExists('questions')) {
            return 0;
        }

        $hasExamId = $this->columnExists('questions', 'exam_id');
        $hasSubjectId = $this->columnExists('questions', 'subject_id');
        $hasTopicId = $this->columnExists('questions', 'topic_id');
        $hasCorporationId = $this->columnExists('questions', 'corporation_id');

        if (! $hasExamId && (empty($subjectIds) || ! $hasSubjectId)) {
            return 0;
        }

        $query = DB::table('questions');

        if ($this->columnExists('questions', 'status')) {
            $query->where('status', 'published');
        }

        $query->where(function ($where) use ($exam, $subjectIds, $topicIds, $hasExamId, $hasSubjectId, $hasTopicId, $hasCorporationId) {
            $addedCondition = false;

            if ($hasExamId) {
                $where->where('exam_id', (int) $exam->id);
                $addedCondition = true;
            }

            if (! empty($subjectIds) && $hasSubjectId) {
                $method = $addedCondition ? 'orWhere' : 'where';

                $where->{$method}(function ($subjectQuery) use ($exam, $subjectIds, $topicIds, $hasTopicId, $hasCorporationId) {
                    $subjectQuery->whereIn('subject_id', $subjectIds);

                    if (! empty($topicIds) && $hasTopicId) {
                        $subjectQuery->whereIn('topic_id', $topicIds);
                    }

                    if ($hasCorporationId && ! empty($exam->corporation_id)) {
                        $subjectQuery->where(function ($corporationQuery) use ($exam) {
                            $corporationQuery
                                ->whereNull('corporation_id')
                                ->orWhere('corporation_id', (int) $exam->corporation_id);
                        });
                    }
                });
            }
        });

        return (int) $query->distinct('questions.id')->count('questions.id');
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return $this->tableExists($table) && Schema::hasColumn($table, $column);
    }

    private function countTable(string $table): int
    {
        if (! $this->tableExists($table)) {
            return 0;
        }

        return (int) DB::table($table)->count();
    }

    private function countWhere(string $table, string $column, mixed $value): int
    {
        if (! $this->columnExists($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)->where($column, $value)->count();
    }
}
