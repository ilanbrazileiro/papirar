<?php
namespace App\Services\Marketing;
use Illuminate\Support\Facades\Cache;
use Throwable;
class AdminTrafficDashboard {
 public function data():array {
  $ga=app(Ga4AnalyticsService::class);
  if(!($ga->configurationStatus()['configured']??false))return ['available'=>false,'message'=>'GA4 não configurado. Configure a propriedade e as credenciais existentes para ver o tráfego.'];
  try{return Cache::remember('admin_traffic_ga4_7days_v1',now()->addMinutes(45),function()use($ga){
   // Dias completos: evita comparar o dia atual incompleto ao anterior.
   $dates=[];$views=[];$users=[];
   for($i=7;$i>=1;$i--){$day=now()->subDays($i)->format('Y-m-d');$a=$ga->overview($day,$day);$dates[]=$day;$views[]=$a['page_views'];$users[]=$a['total_users'];}
   $from=$dates[0];$to=$dates[6];$summary=$ga->overview($from,$to);
   $sources=$ga->acquisition($from,$to,8);
   return ['available'=>true,'dates'=>$dates,'views'=>$views,'users'=>$users,'summary'=>$summary,'sources'=>$sources,'from'=>$from,'to'=>$to];
  });}catch(Throwable $e){report($e);return ['available'=>false,'message'=>'A consulta do GA4 não está disponível no momento. Os demais indicadores continuam operacionais.'];}
 }
}
