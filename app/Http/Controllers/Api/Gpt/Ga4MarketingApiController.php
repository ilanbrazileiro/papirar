<?php
namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Services\Marketing\Ga4AnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class Ga4MarketingApiController extends Controller
{
    public function __construct(private readonly Ga4AnalyticsService $ga4) {}

    public function health(): JsonResponse
    {
        return response()->json(['ok'=>true,'service'=>'papirar-ga4-data-api','mode'=>'read-only','configuration'=>$this->ga4->configurationStatus(),'generated_at'=>now()->toIso8601String()]);
    }
    public function overview(Request $r): JsonResponse { return $this->respond($r, fn($f,$t)=>['overview'=>$this->ga4->overview($f,$t)]); }
    public function acquisition(Request $r): JsonResponse { $l=min(max((int)$r->integer('limit',25),1),100); return $this->respond($r,fn($f,$t)=>['channels'=>$this->ga4->acquisition($f,$t,$l)]); }
    public function landingPages(Request $r): JsonResponse { $l=min(max((int)$r->integer('limit',25),1),100); return $this->respond($r,fn($f,$t)=>['landing_pages'=>$this->ga4->landingPages($f,$t,$l)]); }
    public function events(Request $r): JsonResponse { $l=min(max((int)$r->integer('limit',50),1),100); return $this->respond($r,fn($f,$t)=>['events'=>$this->ga4->events($f,$t,$l)]); }

    private function respond(Request $r, callable $cb): JsonResponse
    {
        [$f,$t]=$this->period($r);
        try { return response()->json(array_merge(['period'=>['from'=>$f,'to'=>$t],'source'=>'Google Analytics 4 Data API'],$cb($f,$t),['generated_at'=>now()->toIso8601String()])); }
        catch(Throwable $e){ report($e); return response()->json(['message'=>'Não foi possível consultar o Google Analytics 4.','error_type'=>class_basename($e),'configuration'=>$this->ga4->configurationStatus()],502); }
    }
    private function period(Request $r): array
    {
        $v=$r->validate(['from'=>['nullable','date_format:Y-m-d'],'to'=>['nullable','date_format:Y-m-d'],'limit'=>['nullable','integer','min:1','max:100']]);
        $to=isset($v['to'])?CarbonImmutable::createFromFormat('Y-m-d',$v['to']):CarbonImmutable::now();
        $from=isset($v['from'])?CarbonImmutable::createFromFormat('Y-m-d',$v['from']):$to->subDays(29);
        abort_if($from->greaterThan($to),422,'A data inicial não pode ser posterior à data final.');
        abort_if($from->diffInDays($to)>366,422,'O período máximo permitido é de 367 dias.');
        return [$from->toDateString(),$to->toDateString()];
    }
}
