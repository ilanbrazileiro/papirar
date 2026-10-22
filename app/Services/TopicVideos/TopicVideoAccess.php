<?php
namespace App\Services\TopicVideos;
use App\Models\CourseAccess;
use App\Models\TopicVideoLesson;
use App\Models\Course;
use App\Models\User;
class TopicVideoAccess {
 public function allowed(TopicVideoLesson $video,?User $user,Course $course):bool {
  if(!$user || $video->status!=='published')return false;
  if($video->origin==='youtube') return true;
  return CourseAccess::query()->where('user_id',$user->id)->where('course_id',$course->id)->where('status',CourseAccess::STATUS_ACTIVE)
   ->where('access_type',CourseAccess::TYPE_PAID)
   ->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))
   ->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->exists();
 }
}
