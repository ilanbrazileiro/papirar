<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ExtractorApiTool;
use Laravel\Mcp\Server;

class ExtractorServer extends Server
{
    protected string $name = 'Papirar Extrator';

    protected string $version = '0.1.0';

    protected string $instructions = 'Pesquise a taxonomia atual e duplicidades antes de cadastrar. As questões novas são gravadas em rascunho. Lotes aceitam até 20 itens e podem ter sucesso parcial: confira cada resultado e consulte os IDs criados. Este servidor não publica, arquiva nem revisa questões existentes.';

    protected function boot(): void
    {
        $definitions = json_decode(file_get_contents(resource_path('mcp/extractor-tools.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->tools = array_map(fn (array $definition) => new ExtractorApiTool($definition), $definitions);
    }
}
