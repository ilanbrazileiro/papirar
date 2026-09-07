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

class CourseLandingController extends Controller
{
    public function show(string $slug): View
    {
        $course = Course::query()
            ->active()
            ->public()
            ->landingEnabled()
            ->with(['corporation:id,name', 'exam:id,title,year'])
            ->where('slug', $slug)
            ->firstOrFail();

        $scope = $this->resolveCourseScope($course);

        $subjects = Subject::query()
            ->whereIn('id', $scope['subject_ids'] ?: [0])
            ->where('active', true)
            ->withCount(['questions as landing_questions_count' => function ($query) use ($scope) {
                $query->visibleToStudent()
                    ->when($scope['topic_ids'], fn ($q) => $q->whereIn('topic_id', $scope['topic_ids']))
                    ->when($scope['source_material_ids'], fn ($q) => $q->whereIn('source_material_id', $scope['source_material_ids']));
            }])
            ->orderBy('name')
            ->get();

        $topics = Topic::query()
            ->whereIn('id', $scope['topic_ids'] ?: [0])
            ->where('active', true)
            ->with('subject:id,name,slug')
            ->orderBy('name')
            ->get();

        $questions = Question::query()
            ->visibleToStudent()
            ->when($scope['subject_ids'], fn ($q) => $q->whereIn('subject_id', $scope['subject_ids']))
            ->when($scope['topic_ids'], fn ($q) => $q->whereIn('topic_id', $scope['topic_ids']))
            ->when($scope['source_material_ids'], fn ($q) => $q->whereIn('source_material_id', $scope['source_material_ids']));

        $totalQuestions = (clone $questions)->count();

        $demoQuestion = (clone $questions)
            ->when(
                $course->landing_question_id,
                fn ($q) => $q->whereKey($course->landing_question_id)
            )
            ->with(['alternatives', 'subject:id,name,slug', 'topic:id,name,slug', 'examBoard:id,name', 'exam:id,title,year'])
            ->first();

        if (! $demoQuestion && ! $course->landing_question_id) {
            $demoQuestion = (clone $questions)
                ->with(['alternatives', 'subject:id,name,slug', 'topic:id,name,slug', 'examBoard:id,name', 'exam:id,title,year'])
                ->latest('id')
                ->first();
        }

        $canonicalUrl = route('site.course-landings.show', ['slug' => $course->slug]);
        $seoTitle = Str::limit($course->landing_seo_title ?: ($course->landing_headline ?: $course->title) . ' | Papirar', 70, '');
        $seoDescription = Str::limit(
            $course->landing_seo_description ?: $course->landing_subheadline ?: $course->short_description ?: $course->commercialHeadline(),
            170,
            ''
        );

        return view('site.course-landings.show', compact(
            'course', 'subjects', 'topics', 'totalQuestions', 'demoQuestion',
            'canonicalUrl', 'seoTitle', 'seoDescription'
        ));
    }

    private function resolveCourseScope(Course $course): array
    {
        if ($course->inherit_exam_scope && $course->exam_id) {
            $subjectIds = DB::table('exam_subjects')->where('exam_id', $course->exam_id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->all();
            $topicIds = DB::table('exam_subject_topics')->join('exam_subjects', 'exam_subject_topics.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_topics.is_active', true)->pluck('exam_subject_topics.topic_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
            $sourceIds = DB::table('exam_subject_source_materials')->join('exam_subjects', 'exam_subject_source_materials.exam_subject_id', '=', 'exam_subjects.id')->where('exam_subjects.exam_id', $course->exam_id)->where('exam_subjects.is_active', true)->where('exam_subject_source_materials.is_active', true)->pluck('exam_subject_source_materials.source_material_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

            return ['subject_ids' => $subjectIds, 'topic_ids' => $topicIds, 'source_material_ids' => $sourceIds];
        }

        return [
            'subject_ids' => DB::table('course_subjects')->where('course_id', $course->id)->where('is_active', true)->pluck('subject_id')->map(fn ($id) => (int) $id)->all(),
            'topic_ids' => DB::table('course_topics')->where('course_id', $course->id)->where('is_active', true)->pluck('topic_id')->map(fn ($id) => (int) $id)->all(),
            'source_material_ids' => DB::table('course_source_materials')->where('course_id', $course->id)->where('is_active', true)->pluck('source_material_id')->map(fn ($id) => (int) $id)->all(),
        ];
    }
}
