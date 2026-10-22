<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TopicVideoLesson;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class TopicVideoLessonController extends Controller {
 public function index(){
  abort_unless(auth()->user()?->role==='admin',403);
  return view('admin.topic-videos.index',['lessons'=>TopicVideoLesson::with(['subject','topic'])->orderByDesc('id')->paginate(30),'subjects'=>Subject::orderBy('name')->get(['id','name']),'topics'=>Topic::orderBy('name')->get(['id','subject_id','name'])]);
 }
 public function save(Request $request){
  abort_unless(auth()->user()?->role==='admin',403);
  $data=$request->validate(['id'=>'nullable|integer|exists:topic_video_lessons,id','subject_id'=>'required|integer|exists:subjects,id','topic_id'=>'required|integer|exists:topics,id','title'=>'required|string|max:255','description'=>'nullable|string|max:5000','teacher'=>'nullable|string|max:180','channel_name'=>'nullable|string|max:180','origin'=>'required|in:youtube,papirar','video_url'=>'required|url|max:500','status'=>'required|in:draft,published,unavailable','sort_order'=>'required|integer|min:0|max:99999','is_featured'=>'nullable|boolean','last_reviewed_at'=>'nullable|date']);
  abort_unless(Topic::whereKey($data['topic_id'])->where('subject_id',$data['subject_id'])->exists(),422,'Tópico não pertence à disciplina.');
  if($data['origin']==='youtube'){
   $url=$data['video_url'];$parts=parse_url($url);$host=strtolower($parts['host']??'');$path=trim($parts['path']??'','/');parse_str($parts['query']??'',$query);
   if(!in_array($host,['www.youtube.com','youtube.com','m.youtube.com','youtu.be','www.youtube-nocookie.com'],true))throw ValidationException::withMessages(['video_url'=>'Use um link oficial do YouTube.']);
   $id=$host==='youtu.be'?explode('/',$path)[0]:($query['v']??(Str::startsWith($path,'shorts/')||Str::startsWith($path,'embed/')?explode('/',$path)[1]??'':''));
   if(!preg_match('/^[A-Za-z0-9_-]{11}$/',(string)$id))throw ValidationException::withMessages(['video_url'=>'ID do vídeo do YouTube inválido.']);
   $data['youtube_id']=$id;
  } else { $data['youtube_id']=null; if(!str_starts_with($data['video_url'],'https://'))throw ValidationException::withMessages(['video_url'=>'Vídeo próprio exige endereço HTTPS de hospedagem autorizada.']); }
  $id=$data['id']??null;unset($data['id']);$data['is_featured']=$request->boolean('is_featured');
  TopicVideoLesson::updateOrCreate(['id'=>$id],$data);
  return redirect()->route('admin.topic-videos.index')->with('success','Videoaula salva.');
 }
 public function destroy(TopicVideoLesson $video){abort_unless(auth()->user()?->role==='admin',403);$video->update(['status'=>'draft']);return back()->with('success','Aula despublicada.');}
}
