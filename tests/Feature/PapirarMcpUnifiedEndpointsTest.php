<?php

namespace Tests\Feature;

use App\Mcp\Servers\PapirarServer;
use Tests\TestCase;

class PapirarMcpUnifiedEndpointsTest extends TestCase
{
    public function test_both_mcp_urls_use_the_unified_server(): void
    {
        $source = file_get_contents(base_path('routes/ai.php'));

        foreach (['/mcp/extractor', '/mcp/papirar'] as $path) {
            $this->assertStringContainsString(
                "Mcp::web('{$path}', PapirarServer::class)",
                $source
            );
        }
        $this->assertStringNotContainsString('ExtractorServer::class', $source);
    }
}
