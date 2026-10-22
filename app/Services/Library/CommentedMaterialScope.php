<?php
namespace App\Services\Library;

use App\Models\Course;
use App\Models\LibraryDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CommentedMaterialScope
{
    public function forCourse(Course $course): array
    {
        return app(\App\Services\TopicVideos\TopicVideoScope::class)->forCourse($course);
    }

    public function queryForCourse(Course $course): Builder
    {
        $scope = $this->forCourse($course);
        return LibraryDocument::query()->where('type', 'commented_pdf')
            ->whereNotNull('subject_id')->where('is_published', true)
            ->whereIn('subject_id', $scope['subjects'])
            ->whereHas('topics', fn (Builder $q) => $q->whereIn('topics.id', $scope['topics']));
    }

    public function matchingCourseIds(LibraryDocument $document): array
    {
        if ($document->type !== 'commented_pdf' || !$document->subject_id) return [];
        $topicIds = $document->topics()->pluck('topics.id')->all();
        if (!$topicIds) return [];
        // A mesma regra de escopo é utilizada na biblioteca e no download.
        return Course::query()->where('active', true)->where('is_public', true)->get(['id','inherit_exam_scope','exam_id'])
            ->filter(function (Course $course) use ($document, $topicIds) {
                $scope = $this->forCourse($course);
                return in_array((int)$document->subject_id, $scope['subjects'], true)
                    && count(array_intersect($topicIds, $scope['topics'])) > 0;
            })->pluck('id')->all();
    }
}
