<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TopicVideoLesson;
use App\Services\TopicVideos\TopicVideoAccess;
use Illuminate\Http\Request;
class TopicVideoController extends Controller {
 public function show(Request $request, Course $course,TopicVideoLesson $video,TopicVideoAccess $access){
  abort_unless($request->user(),403);
  abort_unless($course->active && $course->is_public,404);
  abort_unless($video->status==='published',404);
  $scope=app(\App\Services\TopicVideos\TopicVideoScope::class)->forCourse($course);
  abort_unless(in_array($video->subject_id,$scope['subjects']) && in_array($video->topic_id,$scope['topics']),404);
  abort_unless($access->allowed($video,$request->user(),$course),403);
  return view('student.courses.topic-video',['course'=>$course,'video'=>$video]);
 }
}
