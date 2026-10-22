<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LibraryDocument;
use App\Services\Library\CommentedMaterialScope;
use App\Services\Library\LibraryDownloadAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CourseLibraryController extends Controller
{
    public function index(Request $request, Course $course, LibraryDownloadAccess $permissions): View
    {
        abort_unless($course->active && $course->is_public,404);
        $exams=LibraryDocument::query()->where('course_id',$course->id)->where('type','official_exam')->where('is_published',true)->get();
        $materials=app(CommentedMaterialScope::class)->queryForCourse($course)->with(['subject','topics'])->get();
        $documents=$exams->concat($materials)->sortByDesc('id')->values();
        $allowed=[];
        foreach($documents as $document) $allowed[$document->id]=$permissions->allowed($document,$request->user());
        $scope=app(\App\Services\TopicVideos\TopicVideoScope::class)->forCourse($course);
        $topicVideos=\App\Models\TopicVideoLesson::with(['subject','topic'])
            ->where('status','published')->whereIn('subject_id',$scope['subjects'])->whereIn('topic_id',$scope['topics'])
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('id')->get();
        $videoAllowed=[];$videoAccess=app(\App\Services\TopicVideos\TopicVideoAccess::class);
        foreach($topicVideos as $video) $videoAllowed[$video->id]=$videoAccess->allowed($video,$request->user(),$course);
        return view('student.courses.materials',compact('course','documents','allowed','topicVideos','videoAllowed'));
    }
}
