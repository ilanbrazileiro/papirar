<?php
namespace App\Services\Marketing;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminTrafficDashboard
{
    public function data(array $filters): array
    {
        $today = now(config('app.timezone'))->startOfDay();
        $period = $filters['period'];
        $group = $filters['group'];
        [$from, $to] = match ($period) {
            'today' => [$today->copy(), $today->copy()],
            '7' => [$today->copy()->subDays(7), $today->copy()->subDay()],
            '30' => [$today->copy()->subDays(30), $today->copy()->subDay()],
            'month' => [$today->copy()->startOfMonth(), $today->copy()],
            default => [Carbon::createFromFormat('!Y-m-d', $filters['from'] ?? '2000-01-01'), Carbon::createFromFormat('!Y-m-d', $filters['to'] ?? '2000-01-01')],
        };
        if ($period === 'custom' && (empty($filters['from']) || empty($filters['to']))) throw ValidationException::withMessages(['from'=>'Informe as datas inicial e final.']);
        if ($from->gt($to) || $to->gt($today) || $from->lt($today->copy()->subYears(5)) || $from->diffInDays($to) > 366) {
            throw ValidationException::withMessages(['from'=>'Selecione um intervalo válido, de até 366 dias, sem datas futuras.']);
        }
        if ($group === 'day' && $from->diffInDays($to) > 93) {
            throw ValidationException::withMessages(['group'=>'Para mais de 94 dias, selecione o agrupamento mensal.']);
        }
        $ga = app(Ga4AnalyticsService::class);
        if (!($ga->configurationStatus()['configured'] ?? false)) return ['available'=>false,'message'=>'GA4 não configurado; verifique as credenciais e a propriedade.'];
        $f=$from->format('Y-m-d'); $t=$to->format('Y-m-d');
        try {
            return Cache::remember('admin_traffic_v2_'.sha1($f.'|'.$t.'|'.$group),now()->addMinutes(30),function () use ($ga,$f,$t,$group) {
                return [
                    'available'=>true,'from'=>$f,'to'=>$t,'group'=>$group,
                    'summary'=>$ga->overview($f,$t),
                    'sources'=>$ga->acquisition($f,$t,10),
                    'series'=>app(Ga4TrafficQuery::class)->timeline($f,$t,$group),
                ];
            });
        } catch (Throwable $e) {
            report($e);
            return ['available'=>false,'message'=>'Não foi possível consultar o GA4 neste momento. A dashboard permanece disponível.'];
        }
    }
}
