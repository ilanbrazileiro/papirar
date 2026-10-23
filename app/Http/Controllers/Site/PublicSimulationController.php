<?php
namespace App\Http\Controllers\Site;
use App\Http\Controllers\Controller;
use App\Models\PublicSimulation;
use App\Models\PublicSimulationAttempt;
use App\Models\Question;
use App\Services\PublicSimulations\PublicSimulationQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class PublicSimulationController extends Controller {
 private function visible(string $slug):PublicSimulation{return PublicSimulation::query()->where('slug',$slug)->where('is_published',true)->whereHas('course',fn($q)=>$q->where('active',true)->where('is_public',true))->with('course')->firstOrFail();}
 public function index(Request $request){$simulations=PublicSimulation::query()->where('is_published',true)->whereHas('course',fn($q)=>$q->where('active',true)->where('is_public',true))->with('course')->withCount('questions')->latest()->paginate(15);return view('site.public-simulations.index',compact('simulations'));}
 public function landing(Request $request,string $slug,PublicSimulationQuota $quota){
  $sim=$this->visible($slug);$visitor=$quota->visitor($request);$quota->sync($request,$visitor);
  $existing=$this->attempts($request,$visitor)->where('public_simulation_id',$sim->id)->latest('id')->first();
  $count=$quota->consumed($request,$visitor);$subscribed=$quota->subscribed($request,(int)$sim->course_id);
  return view('site.public-simulations.landing',compact('sim','existing','count','subscribed'));
 }
 private function attempts(Request $request,string $visitor){return PublicSimulationAttempt::query()->where(function($q)use($request,$visitor){$q->where('visitor_hash',$visitor);if($request->user())$q->orWhere('user_id',$request->user()->id);});}
 public function start(Request $request,string $slug,PublicSimulationQuota $quota){
  $sim=$this->visible($slug);$visitor=$quota->visitor($request);$quota->sync($request,$visitor);
  $existing=$this->attempts($request,$visitor)->where('public_simulation_id',$sim->id)->latest('id')->first();
  // Tentativa em andamento sempre deve ser retomada, nunca sobrescrita.
  if($existing && !$existing->completed_at)return redirect()->route('public-simulations.take',$sim->slug);
  // Repetição exige ação explícita, mantendo o resultado anterior disponível.
  if($existing && !$request->boolean('replay'))return redirect()->route('public-simulations.result',$sim->slug);
  // Repetir um simulado já liberado não consome novo crédito gratuito.
  if(!$existing && !$quota->subscribed($request,(int)$sim->course_id)&&$quota->consumed($request,$visitor)>=$quota->limit($request))
    return redirect()->route('public-simulations.landing',$sim->slug)->with('error',$request->user()?'Seu limite gratuito acabou. Assine o curso para realizar mais simulados.':'Cadastre-se para liberar seu segundo simulado.');
  $count=$sim->questions()->count();abort_if($count<1,422,'Simulado sem questões.');
  $token=Str::random(64);
  PublicSimulationAttempt::query()->create(['public_simulation_id'=>$sim->id,'user_id'=>$request->user()?->id,'visitor_hash'=>$visitor,
   'attempt_token_hash'=>hash('sha256',$token),'question_count'=>$count,'started_at'=>now(),'source'=>Str::limit((string)$request->query('utm_source','direto'),80)]);
  // Apenas um segredo aleatório permanece no navegador; o banco armazena somente hash.
  $request->session()->put('public_sim_token_'.$sim->id,$token);
  return redirect()->route('public-simulations.take',$sim->slug);
 }
 private function attempt(Request $request,PublicSimulation $sim,PublicSimulationQuota $quota):PublicSimulationAttempt {
  $visitor=$quota->visitor($request);$quota->sync($request,$visitor);
  return $this->attempts($request,$visitor)->where('public_simulation_id',$sim->id)->latest('id')->firstOrFail();
 }
 public function take(Request $request,string $slug,PublicSimulationQuota $quota){
  $sim=$this->visible($slug);$attempt=$this->attempt($request,$sim,$quota);
  if($attempt->completed_at)return redirect()->route('public-simulations.result',$slug);
  $questions=$sim->questions()->with('alternatives')->get();
  // Apenas as informações necessárias à realização são exibidas; sem gabarito no HTML.
  $questions->each(fn($q)=>$q->alternatives->each(fn($a)=>$a->makeHidden(['is_correct'])));
  $deadline=$attempt->started_at->copy()->addMinutes($sim->duration_minutes);
  if(now()->greaterThanOrEqualTo($deadline))return $this->complete($request,$sim,$attempt,[]);
  return view('site.public-simulations.take',compact('sim','attempt','questions','deadline'));
 }
 public function finish(Request $request,string $slug,PublicSimulationQuota $quota){
  $sim=$this->visible($slug);$attempt=$this->attempt($request,$sim,$quota);
  if($attempt->completed_at)return redirect()->route('public-simulations.result',$slug);
  $data=$request->validate(['answers'=>'sometimes|array|max:120','answers.*'=>'nullable|integer']);
  return $this->complete($request,$sim,$attempt,$attempt->started_at->copy()->addMinutes($sim->duration_minutes)->isPast()?[]:($data['answers']??[]));
 }
 private function complete(Request $request,PublicSimulation $sim,PublicSimulationAttempt $attempt,array $input){
  $questions=$sim->questions()->with('alternatives')->get();$answers=[];$correct=0;
  foreach($questions as $q){$selected=(int)($input[$q->id]??0);$valid=$q->alternatives->firstWhere('id',$selected);$answers[(string)$q->id]=$valid?$selected:null;
   if($valid&&$valid->is_correct)$correct++;}
  DB::transaction(function()use($attempt,$answers,$correct){
   $locked=PublicSimulationAttempt::query()->lockForUpdate()->findOrFail($attempt->id);
   if(!$locked->completed_at)$locked->update(['answers_json'=>$answers,'correct_answers'=>$correct,'completed_at'=>now()]);
  });
  return redirect()->route('public-simulations.result',$sim->slug);
 }
 public function result(Request $request,string $slug,PublicSimulationQuota $quota){
  $sim=$this->visible($slug);$attempt=$this->attempt($request,$sim,$quota);
  $visitor=$quota->visitor($request);
  $history=$this->attempts($request,$visitor)->where('public_simulation_id',$sim->id)
    ->whereNotNull('completed_at')->orderByDesc('id')->get(['id','completed_at','correct_answers','question_count']);
  if($request->filled('attempt')){
    $selected=$history->firstWhere('id',$request->integer('attempt'));
    abort_unless($selected,404);
    $attempt=$this->attempts($request,$visitor)->where('public_simulation_id',$sim->id)->findOrFail($selected->id);
  }
  if(!$attempt->completed_at)return redirect()->route('public-simulations.take',$slug);
  $questions=$sim->questions()->with('alternatives')->get();return view('site.public-simulations.result',compact('sim','attempt','questions'));
 }
}
