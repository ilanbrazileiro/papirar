<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\PapirarApiTool;
use Laravel\Mcp\Server;

class PapirarServer extends Server
{
    protected string $name = 'Papirar';

    protected string $version = '2.0.0';

    protected string $instructions = 'Servidor MCP unificado do Papirar. Consulte dados atuais antes de escrever. Questões extraídas devem nascer em rascunho. Operações destrutivas, arquivamento, desativação, publicação/ativação de cursos, alterações de preço, merges e alterações em massa exigem confirmação explícita do usuário antes da chamada. Nunca invente IDs: pesquise a taxonomia e os registros atuais.';

    protected function boot(): void
    {
        $files = [
            'extractor-tools.json',
            'reviewer-tools.json',
            'taxonomy-tools.json',
            'catalog-tools.json',
            'course-tools.json',
            'marketing-tools.json',
            'engagement-tools.json',
        ];

        $definitions = [];

        foreach ($files as $file) {
            $path = resource_path('mcp/'.$file);
            if (! is_file($path)) {
                continue;
            }

            $items = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $definitions = array_merge($definitions, $items);
        }

        $names = array_column($definitions, 'name');
        if (count($names) !== count(array_unique($names))) {
            throw new \RuntimeException('Existem nomes de ferramentas MCP duplicados no PapirarServer.');
        }

        $this->tools = array_map(
            fn (array $definition) => new PapirarApiTool($definition),
            $definitions,
        );
    }
}
