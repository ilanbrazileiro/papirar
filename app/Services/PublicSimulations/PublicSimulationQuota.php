<?php
namespace App\Services\PublicSimulations;
use App\Models\CourseAccess;
use App\Models\PublicSimulationAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
class PublicSimulationQuota {
 public const COOKIE='papirar_sim_visitor';
 public function visitor(Request $request):string {
   $token=(string)$request->cookie(self::COOKIE,'');
   if (!preg_match('/^[a-f0-9]{64}$/D',$token)) {
     $token=bin2hex(random_bytes(32));
     Cookie::queue(cookie(self::COOKIE,$token,60*24*365,'/',null,$request->isSecure(),true,false,'lax'));
   }
   return hash_hmac('sha256',$token,(string)config('app.key'));
 }
 public function sync(Request $request,string $hash):void {
   if(!$request->user())return;
   // Atribui as tentativas anônimas ao aluno no primeiro acesso autenticado.
   PublicSimulationAttempt::query()->where('visitor_hash',$hash)->whereNull('user_id')->update(['user_id'=>$request->user()->id]);
 }
 public function subscribed(Request $request,int $courseId):bool {
   if(!$request->user())return false;
   return CourseAccess::query()->where('user_id',$request->user()->id)->where('course_id',$courseId)
    ->where('access_type',CourseAccess::TYPE_PAID)->where('status',CourseAccess::STATUS_ACTIVE)
    ->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))
    ->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->exists();
 }
 public function consumed(Request $request,string $hash):int {
   $query=PublicSimulationAttempt::query()->where(function($q)use($request,$hash){
      $q->where('visitor_hash',$hash);
      if($request->user())$q->orWhere('user_id',$request->user()->id);
   });
   // Cada simulado distinto consome um crédito. Iniciar conta como consumo.
   return (int)$query->distinct()->count('public_simulation_id');
 }
 public function limit(Request $request):int {return $request->user()?2:1;}
}
