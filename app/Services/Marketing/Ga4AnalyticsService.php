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
    private ?string $credentialsBase64;

    public function __construct()
    {
        $this->propertyId = trim((string) config('services.analytics.ga4_property_id'));
        $path = trim((string) config('services.analytics.ga4_credentials_path'));
        $this->credentialsPath = $path === '' ? null : (str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path));
        $base64 = trim((string) config('services.analytics.ga4_credentials_base64'));
        $this->credentialsBase64 = $base64 === '' ? null : $base64;
    }

    public function configurationStatus(): array
    {
        $fileExists = $this->credentialsPath !== null && is_file($this->credentialsPath);
        $fileReadable = $fileExists && is_readable($this->credentialsPath);
        $inlineValid = $this->decodeInlineCredentials() !== null;

        return [
            'configured' => $this->propertyId !== '' && ($fileReadable || $inlineValid),
            'property_id_configured' => $this->propertyId !== '',
            'credentials_configured' => $this->credentialsPath !== null || $this->credentialsBase64 !== null,
            'credentials_file_exists' => $fileExists,
            'credentials_file_readable' => $fileReadable,
            'credentials_base64_configured' => $this->credentialsBase64 !== null,
            'credentials_base64_valid' => $inlineValid,
            'credential_source' => $fileReadable ? 'file' : ($inlineValid ? 'environment' : 'unavailable'),
        ];
    }

    public function overview(string $from, string $to): array
    {
        $rows = $this->run($from, $to, [], ['activeUsers', 'totalUsers', 'newUsers', 'sessions', 'screenPageViews', 'engagementRate', 'averageSessionDuration', 'keyEvents'], 1);
        $m = $rows[0]['metrics'] ?? [];

        return [
            'active_users' => (int) ($m['activeUsers'] ?? 0),
            'total_users' => (int) ($m['totalUsers'] ?? 0),
            'new_users' => (int) ($m['newUsers'] ?? 0),
            'sessions' => (int) ($m['sessions'] ?? 0),
            'page_views' => (int) ($m['screenPageViews'] ?? 0),
            'engagement_rate' => round((float) ($m['engagementRate'] ?? 0), 4),
            'average_session_duration_seconds' => round((float) ($m['averageSessionDuration'] ?? 0), 2),
            'key_events' => (float) ($m['keyEvents'] ?? 0),
        ];
    }

    public function acquisition(string $from, string $to, int $limit): array
    {
        return $this->run($from, $to, ['sessionSource', 'sessionMedium', 'sessionCampaignName'], ['sessions', 'totalUsers', 'newUsers', 'keyEvents'], $limit, 'sessions');
    }

    public function landingPages(string $from, string $to, int $limit): array
    {
        return $this->run($from, $to, ['landingPagePlusQueryString'], ['sessions', 'totalUsers', 'newUsers', 'engagementRate', 'keyEvents'], $limit, 'sessions');
    }

    public function events(string $from, string $to, int $limit): array
    {
        return $this->run($from, $to, ['eventName'], ['eventCount', 'totalUsers'], $limit, 'eventCount');
    }

    private function decodeInlineCredentials(): ?array
    {
        if ($this->credentialsBase64 === null) {
            return null;
        }

        $raw = base64_decode($this->credentialsBase64, true);
        if ($raw === false) {
            return null;
        }

        $value = json_decode($raw, true);
        return is_array($value) && ($value['type'] ?? null) === 'service_account'
            && ! empty($value['client_email']) && ! empty($value['private_key']) ? $value : null;
    }

    private function client(): BetaAnalyticsDataClient
    {
        if ($this->propertyId === '') {
            throw new RuntimeException('GA4_PROPERTY_ID não configurado.');
        }

        // Prioridade para o arquivo existente, preservando o comportamento anterior.
        if ($this->credentialsPath !== null && is_file($this->credentialsPath) && is_readable($this->credentialsPath)) {
            return new BetaAnalyticsDataClient(['credentials' => $this->credentialsPath]);
        }

        $inline = $this->decodeInlineCredentials();
        if ($inline !== null) {
            return new BetaAnalyticsDataClient(['credentials' => $inline]);
        }

        throw new RuntimeException('Credenciais do GA4 indisponíveis. Configure GA4_CREDENTIALS_PATH com arquivo legível ou GA4_CREDENTIALS_BASE64.');
    }

    private function run(string $from, string $to, array $dims, array $metrics, int $limit = 100, ?string $order = null): array
    {
        $client = $this->client();
        try {
            $req = (new RunReportRequest())
                ->setProperty('properties/'.$this->propertyId)
                ->setDateRanges([new DateRange(['start_date' => $from, 'end_date' => $to])])
                ->setDimensions(array_map(fn ($n) => new Dimension(['name' => $n]), $dims))
                ->setMetrics(array_map(fn ($n) => new Metric(['name' => $n]), $metrics))
                ->setLimit($limit);
            if ($order) {
                $req->setOrderBys([new OrderBy(['metric' => new OrderBy\MetricOrderBy(['metric_name' => $order]), 'desc' => true])]);
            }
            $res = $client->runReport($req);
            $dh = [];
            foreach ($res->getDimensionHeaders() as $h) {
                $dh[] = $h->getName();
            }
            $mh = [];
            foreach ($res->getMetricHeaders() as $h) {
                $mh[] = $h->getName();
            }
            $rows = [];
            foreach ($res->getRows() as $row) {
                $d = [];
                foreach ($row->getDimensionValues() as $i => $v) {
                    $d[$dh[$i] ?? 'dimension_'.$i] = $v->getValue();
                }
                $m = [];
                foreach ($row->getMetricValues() as $i => $v) {
                    $m[$mh[$i] ?? 'metric_'.$i] = $v->getValue();
                }
                $rows[] = ['dimensions' => $d, 'metrics' => $m];
            }
            return $rows;
        } finally {
            $client->close();
        }
    }
}
