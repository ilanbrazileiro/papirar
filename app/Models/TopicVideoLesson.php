<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TopicVideoLesson extends Model {
 protected $fillable=['subject_id','topic_id','title','description','teacher','channel_name','origin','video_url','youtube_id','status','sort_order','is_featured','last_reviewed_at','last_checked_at','availability'];
 protected $casts=['is_featured'=>'boolean','last_reviewed_at'=>'datetime','last_checked_at'=>'datetime'];
 public function subject(){return $this->belongsTo(Subject::class);}
 public function topic(){return $this->belongsTo(Topic::class);}
 public function embedUrl():?string {
  if($this->origin==='youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/',(string)$this->youtube_id)) return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id;
  if($this->origin==='papirar') return filter_var($this->video_url,FILTER_VALIDATE_URL) && str_starts_with($this->video_url,'https://') ? $this->video_url : null; // URL de player autorizado, sem proteção DRM própria
  return null;
 }
}
