<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PapirarMarketingMcpTest extends TestCase
{
    private static array $testKeys = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32)), 'cache.default' => 'array']);

        if (self::$testKeys === []) {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
            openssl_pkey_export($key, $private);
            self::$testKeys = [$private, openssl_pkey_get_details($key)['key']];
        }

        config(['passport.private_key' => self::$testKeys[0], 'passport.public_key' => self::$testKeys[1]]);
    }

    public function test_marketing_tools_are_exposed_as_read_only_and_safe(): void
    {
        $this->signIn();
        $tools = $this->listAllTools();
        $names = array_column($tools, 'name');

        $expected = [
            'marketingHealth',
            'getMarketingFunnel',
            'getMarketingAcquisition',
            'getMarketingCourses',
            'getMarketingRevenue',
            'ga4Health',
            'getGa4Overview',
            'getGa4Acquisition',
            'getGa4LandingPages',
            'getGa4Events',
        ];

        foreach ($expected as $name) {
            $this->assertContains($name, $names);
            $tool = collect($tools)->firstWhere('name', $name);
            $this->assertTrue($tool['annotations']['readOnlyHint']);
            $this->assertFalse($tool['annotations']['destructiveHint']);
            $this->assertFalse($tool['_meta']['confirmationRequired']);
        }
    }

    public function test_marketing_health_can_be_called_through_unified_mcp(): void
    {
        $this->signIn();
        $data = $this->toolData($this->callTool('marketingHealth'));

        $this->assertSame(200, $data['http_status']);
        $this->assertTrue($data['response']['ok']);
        $this->assertSame('papirar-marketing-api', $data['response']['service']);
        $this->assertSame('read-only', $data['response']['mode']);
    }

    private function signIn(): void
    {
        Passport::actingAs(new User(['role' => 'admin', 'is_active' => true]), ['mcp:use'], 'api');
    }

    private function listAllTools(): array
    {
        $tools = [];
        $cursor = null;
        $seen = [];

        do {
            $response = $this->rpc('tools/list', $cursor ? ['cursor' => $cursor] : [])->assertOk();
            $tools = array_merge($tools, $response->json('result.tools') ?? []);
            $next = $response->json('result.nextCursor');

            if (! $next || in_array($next, $seen, true)) {
                break;
            }

            $seen[] = $next;
            $cursor = $next;
        } while (count($seen) < 30);

        return $tools;
    }

    private function rpc(string $method, array $params = [])
    {
        $headers = [
            'Accept' => 'application/json, text/event-stream',
            'MCP-Protocol-Version' => '2025-06-18',
            'Origin' => 'https://chatgpt.com',
        ];

        if ($method === 'tools/call') {
            $headers['Mcp-Name'] = $params['name'];
        }

        return $this->postJson('/mcp/papirar', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => (object) $params,
        ], $headers);
    }

    private function callTool(string $name, array $arguments = [])
    {
        return $this->rpc('tools/call', ['name' => $name, 'arguments' => (object) $arguments])->assertOk();
    }

    private function toolData($response): array
    {
        return json_decode($response->json('result.content.0.text'), true, 512, JSON_THROW_ON_ERROR);
    }
}
