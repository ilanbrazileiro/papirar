<?php
namespace App\Services\Marketing;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use RuntimeException;

class Ga4AnalyticsService
{
    private string $propertyId;
    private ?string $credentialsPath;

    public function __construct()
    {
        $this->propertyId = trim((string) config('services.analytics.ga4_property_id'));
        $path = trim((string) config('services.analytics.ga4_credentials_path'));
        $this->credentialsPath = $path === '' ? null : (str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path));
    }

    public function configurationStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'property_id_configured' => $this->propertyId !== '',
            'credentials_configured' => $this->credentialsPath !== null,
            'credentials_file_exists' => $this->credentialsPath ? is_file($this->credentialsPath) : false,
        ];
    }

    public function overview(string $from, string $to): array
    {
        $rows = $this->run($from,$to,[],['activeUsers','totalUsers','newUsers','sessions','screenPageViews','engagementRate','averageSessionDuration','keyEvents'],1);
        $m=$rows[0]['metrics'] ?? [];
        return [
            'active_users'=>(int)($m['activeUsers'] ?? 0),
            'total_users'=>(int)($m['totalUsers'] ?? 0),
            'new_users'=>(int)($m['newUsers'] ?? 0),
            'sessions'=>(int)($m['sessions'] ?? 0),
            'page_views'=>(int)($m['screenPageViews'] ?? 0),
            'engagement_rate'=>round((float)($m['engagementRate'] ?? 0),4),
            'average_session_duration_seconds'=>round((float)($m['averageSessionDuration'] ?? 0),2),
            'key_events'=>(float)($m['keyEvents'] ?? 0),
        ];
    }

    public function acquisition(string $from,string $to,int $limit): array
    { return $this->run($from,$to,['sessionSource','sessionMedium','sessionCampaignName'],['sessions','totalUsers','newUsers','keyEvents'],$limit,'sessions'); }
    public function landingPages(string $from,string $to,int $limit): array
    { return $this->run($from,$to,['landingPagePlusQueryString'],['sessions','totalUsers','newUsers','engagementRate','keyEvents'],$limit,'sessions'); }
    public function events(string $from,string $to,int $limit): array
    { return $this->run($from,$to,['eventName'],['eventCount','totalUsers'],$limit,'eventCount'); }

    private function isConfigured(): bool
    { return $this->propertyId !== '' && $this->credentialsPath !== null && is_file($this->credentialsPath); }

    private function client(): BetaAnalyticsDataClient
    {
        if (!$this->isConfigured()) throw new RuntimeException('GA4 Data API não configurada.');
        return new BetaAnalyticsDataClient(['credentials'=>$this->credentialsPath]);
    }

    private function run(string $from,string $to,array $dims,array $metrics,int $limit=100,?string $order=null): array
    {
        $client=$this->client();
        try {
            $req=(new RunReportRequest())
                ->setProperty('properties/'.$this->propertyId)
                ->setDateRanges([new DateRange(['start_date'=>$from,'end_date'=>$to])])
                ->setDimensions(array_map(fn($n)=>new Dimension(['name'=>$n]),$dims))
                ->setMetrics(array_map(fn($n)=>new Metric(['name'=>$n]),$metrics))
                ->setLimit($limit);
            if($order){$req->setOrderBys([new OrderBy(['metric'=>new OrderBy\MetricOrderBy(['metric_name'=>$order]),'desc'=>true])]);}
            $res=$client->runReport($req);
            $dh=[]; foreach($res->getDimensionHeaders() as $h){$dh[]=$h->getName();}
            $mh=[]; foreach($res->getMetricHeaders() as $h){$mh[]=$h->getName();}
            $rows=[];
            foreach($res->getRows() as $row){
                $d=[]; foreach($row->getDimensionValues() as $i=>$v){$d[$dh[$i] ?? 'dimension_'.$i]=$v->getValue();}
                $m=[]; foreach($row->getMetricValues() as $i=>$v){$m[$mh[$i] ?? 'metric_'.$i]=$v->getValue();}
                $rows[]=['dimensions'=>$d,'metrics'=>$m];
            }
            return $rows;
        } finally { $client->close(); }
    }
}
