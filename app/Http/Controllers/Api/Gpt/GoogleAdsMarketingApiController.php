<?php

namespace App\Http\Controllers\Api\Gpt;

use App\Http\Controllers\Controller;
use App\Services\Marketing\GoogleAdsReadService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GoogleAdsMarketingApiController extends Controller
{
    public function __construct(private readonly GoogleAdsReadService $googleAds) {}

    public function health(): JsonResponse
    {
        return response()->json(['ok'=>true, 'service'=>'papirar-google-ads-api',
            'health'=>$this->googleAds->health(), 'generated_at'=>now()->toIso8601String()]);
    }

    public function campaigns(Request $request): JsonResponse
    {
        return $this->report($request, fn ($from, $to) => $this->googleAds->campaigns($from, $to));
    }

    public function searchTerms(Request $request): JsonResponse
    {
        return $this->report($request, fn ($from, $to) => $this->googleAds->searchTerms($from, $to));
    }

    private function report(Request $request, callable $handler): JsonResponse
    {
        $valid = $request->validate([
            'from'=>['nullable','date_format:Y-m-d'],
            'to'=>['nullable','date_format:Y-m-d'],
        ]);
        $to = isset($valid['to']) ? CarbonImmutable::createFromFormat('Y-m-d', $valid['to']) : CarbonImmutable::today();
        $from = isset($valid['from']) ? CarbonImmutable::createFromFormat('Y-m-d', $valid['from']) : $to->subDays(29);
        abort_if($from->greaterThan($to),422,'Período inválido.');
        abort_if($from->diffInDays($to)>90,422,'Período máximo de 91 dias.');
        try {
            return response()->json(['source'=>'Google Ads API','period'=>['from'=>$from->toDateString(),'to'=>$to->toDateString()],
                'currency_note'=>'Valores de custo na moeda configurada na conta Google Ads.',
                'rows'=>$handler($from->toDateString(),$to->toDateString()), 'generated_at'=>now()->toIso8601String()]);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message'=>'Não foi possível consultar o Google Ads.',
                'error_type'=>class_basename($e), 'health'=>$this->googleAds->health()],502);
        }
    }
}
