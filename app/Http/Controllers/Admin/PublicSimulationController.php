<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PublicSimulation;
use App\Models\Question;
use App\Services\PublicSimulations\CourseQuestionLottery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class PublicSimulationController extends Controller {
 private function authorizeAdmin(Request $request):void {abort_unless($request->user()?->role==='admin',403);}
 public function index(Request $request){$this->authorizeAdmin($request);return view('admin.public-simulations.index',[
  'simulations'=>PublicSimulation::with('course')->withCount(['questions','attempts','attempts as completed_attempts_count'=>fn($q)=>$q->whereNotNull('completed_at')])->latest()->paginate(25),
 ]);}
 public function create(Request $request){$this->authorizeAdmin($request);return view('admin.public-simulations.form',['courses'=>Course::query()->orderBy('title')->get(['id','title']),'editing'=>null]);}
 public function edit(Request $request,PublicSimulation $simulation){$this->authorizeAdmin($request);return view('admin.public-simulations.form',['courses'=>Course::query()->orderBy('title')->get(['id','title']),'editing'=>$simulation->load('questions')]);}
 public function scope(Request $request, CourseQuestionLottery $lottery)
 {
  $this->authorizeAdmin($request);
  $course=Course::query()->findOrFail($request->integer('course_id'));
  return response()->json(['subjects'=>$lottery->subjects($course)]);
 }
 public function save(Request $request, CourseQuestionLottery $lottery){$this->authorizeAdmin($request);
  $data=$request->validate(['id'=>'nullable|integer|exists:public_simulations,id','course_id'=>'required|integer|exists:courses,id',
  'title'=>'required|string|max:240','slug'=>'nullable|string|max:240','description'=>'nullable|string|max:3000',
  'duration_minutes'=>'required|integer|min:5|max:300','selection_mode'=>'required|in:manual,lottery','question_ids'=>'nullable|string|max:15000',
  'quantities'=>'nullable|array','quantities.*'=>'nullable|integer|min:0|max:120','is_published'=>'nullable|boolean']);
  if($data['selection_mode']==='lottery' && !empty($data['id']) && DB::table('public_simulation_attempts')->where('public_simulation_id',$data['id'])->exists()) throw ValidationException::withMessages(['selection_mode'=>'Este simulado já possui tentativas. Crie outro para sortear novas questões.']);
  $course=Course::query()->findOrFail((int)$data['course_id']);
  $ids=($data['selection_mode']==='lottery')
    ? $lottery->draw($course, $data['quantities']??[])
    : array_values(array_unique(array_map('intval',array_filter(preg_split('/[\s,;]+/',trim((string)($data['question_ids']??'')))))));
  if(count($ids)<1||count($ids)>120)throw ValidationException::withMessages(['question_ids'=>'Informe entre 1 e 120 questões.']);
  $valid=Question::query()->visibleToStudent()->whereIn('id',$ids)->pluck('id')->map(fn($i)=>(int)$i)->all();
  if(count($valid)!==count($ids))throw ValidationException::withMessages(['question_ids'=>'Há questões inexistentes ou não publicadas/revisadas.']);
  $slug=Str::slug(($data['slug']??'')?:$data['title']);
  if(!$slug)throw ValidationException::withMessages(['slug'=>'Informe um título válido.']);
  $model=isset($data['id'])?PublicSimulation::findOrFail($data['id']):new PublicSimulation;
  if(PublicSimulation::query()->where('slug',$slug)->when($model->exists,fn($q)=>$q->whereKeyNot($model->id))->exists())
    throw ValidationException::withMessages(['slug'=>'Este endereço já está sendo utilizado.']);
  DB::transaction(function()use($model,$data,$slug,$ids){
    $wasExisting=$model->exists;
    $hasAttempts=$wasExisting&&DB::table('public_simulation_attempts')->where('public_simulation_id',$model->id)->exists();
    if($hasAttempts){
      if((int)$model->course_id !== (int)$data['course_id'])throw ValidationException::withMessages(['course_id'=>'Não altere o curso de um simulado que já recebeu tentativas.']);
      $existing=DB::table('public_simulation_questions')->where('public_simulation_id',$model->id)->orderBy('position')->pluck('question_id')->map(fn($id)=>(int)$id)->all();
      if($existing!==$ids)throw ValidationException::withMessages(['question_ids'=>'Este simulado já possui tentativas. Crie outro simulado para alterar as questões ou a ordem.']);
    }
    $model->fill(['course_id'=>$data['course_id'],'title'=>$data['title'],'slug'=>$slug,
      'description'=>$data['description']??null,'duration_minutes'=>$data['duration_minutes'],'is_published'=>(bool)($data['is_published']??false)])->save();
    if(!$hasAttempts){$model->questions()->detach();foreach($ids as $position=>$id){
      DB::table('public_simulation_questions')->insert(['public_simulation_id'=>$model->id,'question_id'=>$id,'position'=>$position+1]);
    }}
  });
  return redirect()->route('admin.public-simulations.index')->with('success','Simulado salvo.');
 }
}
