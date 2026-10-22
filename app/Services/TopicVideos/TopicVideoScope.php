<?php
namespace App\Services\TopicVideos;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
class TopicVideoScope {
 public function forCourse(Course $course):array {
  if($course->inherit_exam_scope && $course->exam_id){
   $subjects=DB::table('exam_subjects')->where('exam_id',$course->exam_id)->where('is_active',1)->pluck('subject_id')->map(fn($n)=>(int)$n)->all();
   $topics=DB::table('exam_subject_topics')->join('exam_subjects','exam_subjects.id','=','exam_subject_topics.exam_subject_id')->where('exam_subjects.exam_id',$course->exam_id)->where('exam_subjects.is_active',1)->where('exam_subject_topics.is_active',1)->pluck('exam_subject_topics.topic_id')->map(fn($n)=>(int)$n)->all();
  }else{
   $subjects=DB::table('course_subjects')->where('course_id',$course->id)->where('is_active',1)->pluck('subject_id')->map(fn($n)=>(int)$n)->all();
   $topics=DB::table('course_topics')->where('course_id',$course->id)->where('is_active',1)->pluck('topic_id')->map(fn($n)=>(int)$n)->all();
  }
  return ['subjects'=>$subjects,'topics'=>$topics];
 }
}
