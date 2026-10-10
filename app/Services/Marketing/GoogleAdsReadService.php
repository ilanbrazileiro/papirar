<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleAdsReadService
{
    private function settings(): array
    {
        return config('services.google_ads', []);
    }

    public function health(): array
    {
        $c = $this->settings();
        $checks = [];
        foreach (['customer_id', 'developer_token', 'client_id', 'client_secret', 'refresh_token'] as $key) {
            $checks[$key.'_configured'] = filled($c[$key] ?? null);
        }
        return [
            'configured' => !in_array(false, $checks, true),
            'configuration' => $checks,
            'api_version' => $c['api_version'] ?? 'v25',
            'mode' => 'read-only',
            'note' => 'Verifica configuração local; acesso ao Google Ads é validado nas consultas.',
        ];
    }

    private function accessToken(): string
    {
        $c = $this->settings();
        if (!$this->health()['configured']) {
            throw new RuntimeException('Google Ads API não configurada.');
        }
        $response = Http::asForm()->timeout(20)->post('https://oauth2.googleapis.com/token', [
            'client_id' => $c['client_id'],
            'client_secret' => $c['client_secret'],
            'refresh_token' => $c['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);
        if (!$response->successful() || !is_string($response->json('access_token'))) {
            throw new RuntimeException('Falha na autenticação OAuth com o Google Ads (HTTP '.$response->status().').');
        }
        return $response->json('access_token');
    }

    private function search(string $query): array
    {
        $c = $this->settings();
        $customer = preg_replace('/\D/', '', (string) $c['customer_id']);
        $version = $c['api_version'] ?? 'v25';
        if (!preg_match('/^v\d+$/', (string) $version) || !$customer) {
            throw new RuntimeException('ID da conta ou versão da API inválidos.');
        }
        $request = Http::withToken($this->accessToken())
            ->withHeaders(['developer-token' => $c['developer_token']])
            ->acceptJson()->timeout(40);
        if (filled($c['login_customer_id'] ?? null)) {
            $request = $request->withHeaders([
                'login-customer-id' => preg_replace('/\D/', '', (string) $c['login_customer_id']),
            ]);
        }
        $url = "https://googleads.googleapis.com/{$version}/customers/{$customer}/googleAds:search";
        $rows = [];
        $pageToken = null;
        do {
            $body = ['query' => $query];
            if ($pageToken) $body['pageToken'] = $pageToken;
            $response = $request->post($url, $body);
            if (!$response->successful()) {
                $status = $response->json('error.status', 'UNKNOWN');
                $message = $response->json('error.message', 'Falha na consulta ao Google Ads');
                throw new RuntimeException("Google Ads API HTTP {$response->status()} ({$status}): ".mb_substr((string)$message, 0, 350));
            }
            $payload = $response->json();
            foreach (($payload['results'] ?? []) as $row) $rows[] = $row;
            $pageToken = $payload['nextPageToken'] ?? null;
            if (count($rows) >= 10000) {
                throw new RuntimeException('Relatório ultrapassou limite de 10.000 linhas; reduza o período.');
            }
        } while ($pageToken);
        return $rows;
    }

    private function dates(string $from, string $to): string
    {
        return "segments.date BETWEEN '{$from}' AND '{$to}'";
    }

    public function campaigns(string $from, string $to): array
    {
        $q = 'SELECT campaign.id, campaign.name, campaign.status, '
            .'metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, metrics.conversions_value '
            .'FROM campaign WHERE '.$this->dates($from,$to).' ORDER BY metrics.cost_micros DESC';
        return $this->normalize($this->search($q));
    }

    public function searchTerms(string $from, string $to): array
    {
        $q = 'SELECT campaign.id, campaign.name, search_term_view.search_term, '
            .'metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions '
            .'FROM search_term_view WHERE '.$this->dates($from,$to).' ORDER BY metrics.cost_micros DESC LIMIT 100';
        return $this->normalize($this->search($q));
    }

    private function normalize(array $rows): array
    {
        return array_map(function (array $r): array {
            $m = $r['metrics'] ?? [];
            $costMicros = (float) ($m['costMicros'] ?? 0);
            return [
                'campaign_id' => $r['campaign']['id'] ?? null,
                'campaign_name' => $r['campaign']['name'] ?? null,
                'campaign_status' => $r['campaign']['status'] ?? null,
                'search_term' => $r['searchTermView']['searchTerm'] ?? null,
                'impressions' => (int) ($m['impressions'] ?? 0),
                'clicks' => (int) ($m['clicks'] ?? 0),
                'cost' => round($costMicros / 1000000, 2),
                'conversions' => (float) ($m['conversions'] ?? 0),
                'conversion_value' => (float) ($m['conversionsValue'] ?? 0),
            ];
        }, $rows);
    }
}
