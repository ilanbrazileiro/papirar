<?php
namespace App\Services\Marketing;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use RuntimeException;

class Ga4TrafficQuery
{
    public function timeline(string $from, string $to, string $group): array
    {
        $property = trim((string) config('services.analytics.ga4_property_id'));
        $path = trim((string) config('services.analytics.ga4_credentials_path'));
        $base64 = trim((string) config('services.analytics.ga4_credentials_base64'));
        $credentials = null;
        if ($path !== '') {
            $full = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);
            if (is_file($full) && is_readable($full)) $credentials = $full;
        }
        if ($credentials === null && $base64 !== '') {
            $decoded = json_decode((string) base64_decode($base64, true), true);
            if (is_array($decoded) && ($decoded['type'] ?? null) === 'service_account') $credentials = $decoded;
        }
        if (!$property || !$credentials) throw new RuntimeException('GA4 não configurado.');
        $client = new BetaAnalyticsDataClient(['credentials' => $credentials]);
        try {
            $dimension = $group === 'month' ? 'yearMonth' : 'date';
            $request = (new RunReportRequest())
                ->setProperty('properties/'.$property)
                ->setDateRanges([new DateRange(['start_date'=>$from,'end_date'=>$to])])
                ->setDimensions([new Dimension(['name'=>$dimension])])
                ->setMetrics([new Metric(['name'=>'screenPageViews']),new Metric(['name'=>'totalUsers'])])
                ->setOrderBys([new OrderBy(['dimension'=>new OrderBy\DimensionOrderBy(['dimension_name'=>$dimension]),'desc'=>false])])
                ->setLimit(400);
            $result = $client->runReport($request);
            $rows = [];
            foreach ($result->getRows() as $row) {
                $key = $row->getDimensionValues()[0]->getValue();
                $metrics = $row->getMetricValues();
                $label = $group === 'month' ? substr($key,4,2).'/'.substr($key,0,4) : substr($key,6,2).'/'.substr($key,4,2);
                $rows[] = ['key'=>$key,'label'=>$label,'views'=>(int)$metrics[0]->getValue(),'users'=>(int)$metrics[1]->getValue()];
            }
            return $rows;
        } finally { $client->close(); }
    }
}
