<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\PublicQuestionUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicCourseController extends Controller
{
    public function show(string $slug): View
    {
        $course = Course::query()
            ->active()
            ->public()
            ->with([
                'corporation:id,name',
                'exam:id,title,year',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $scope = $this->resolveCourseScope($course);

        $subjects = Subject::query()
            ->whereIn('id', $scope['subject_ids'] ?: [0])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $topics = Topic::query()
            ->whereIn('id', $scope['topic_ids'] ?: [0])
            ->where('active', true)
            ->with('subject:id,name,slug')
            ->orderBy('name')
            ->get();

        $questionsQuery = Question::query()
            ->visibleToStudent();

        if (!empty($scope['subject_ids'])) {
            $questionsQuery->whereIn('subject_id', $scope['subject_ids']);
        }

        if (!empty($scope['topic_ids'])) {
            $questionsQuery->whereIn('topic_id', $scope['topic_ids']);
        }

        if (!empty($scope['source_material_ids'])) {
            $questionsQuery->whereIn('source_material_id', $scope['source_material_ids']);
        }

        $totalQuestions = (clone $questionsQuery)->count();

        $sampleQuestions = (clone $questionsQuery)
            ->with([
                'subject:id,name,slug',
                'topic:id,name,slug',
                'examBoard:id,name',
                'exam:id,title,year',
            ])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function (Question $question) {
                $plain = html_entity_decode(
                    (string) $question->statement,
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                $plain = strip_tags($plain);
                $plain = preg_replace('/\s+/u', ' ', $plain) ?: '';

                return [
                    'url' => PublicQuestionUrl::url($question),
                    'statement' => Str::limit(trim($plain), 190),
                    'subject' => $question->subject?->name,
                    'topic' => $question->topic?->name,
                    'board' => $question->examBoard?->name,
                    'year' => $question->exam?->year,
                ];
            });

        $canonicalUrl = route('site.courses.show', ['slug' => $course->slug]);

        $seoTitle = Str::limit(
            ($course->sales_headline ?: $course->title) . ' | Papirar',
            65,
            ''
        );

        $seoDescription = Str::limit(
            $course->short_description
                ?: $course->commercialHeadline()
                ?: ('Estude para ' . $course->title . ' resolvendo questões no Papirar.'),
            158,
            ''
        );

        return view('site.courses.show', [
            'course' => $course,
            'subjects' => $subjects,
            'topics' => $topics,
            'totalQuestions' => $totalQuestions,
            'sampleQuestions' => $sampleQuestions,
            'canonicalUrl' => $canonicalUrl,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
        ]);
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            $subjectIds = DB::table('exam_subjects')
                ->where('exam_id', $course->exam_id)
                ->where('is_active', true)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $topicIds = DB::table('exam_subject_topics')
                ->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')
                ->where('exam_subjects.exam_id', $course->exam_id)
                ->where('exam_subjects.is_active', true)
                ->where('exam_subject_topics.is_active', true)
                ->pluck('exam_subject_topics.topic_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $sourceMaterialIds = DB::table('exam_subject_source_materials')
                ->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')
                ->where('exam_subjects.exam_id', $course->exam_id)
                ->where('exam_subjects.is_active', true)
                ->where('exam_subject_source_materials.is_active', true)
                ->pluck('exam_subject_source_materials.source_material_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            return [
                'subject_ids' => $subjectIds,
                'topic_ids' => $topicIds,
                'source_material_ids' => $sourceMaterialIds,
            ];
        }

        return [
            'subject_ids' => DB::table('course_subjects')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),

            'topic_ids' => DB::table('course_topics')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('topic_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),

            'source_material_ids' => DB::table('course_source_materials')
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->pluck('source_material_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
        ];
    }
}
