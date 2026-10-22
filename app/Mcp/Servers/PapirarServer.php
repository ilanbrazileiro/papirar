<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\PapirarApiTool;
use Laravel\Mcp\Server;

class PapirarServer extends Server
{
    protected string $name = 'Papirar';

    protected string $version = '2.0.2';

    protected string $instructions = 'Servidor MCP unificado do Papirar. Consulte dados atuais antes de escrever. Questões extraídas devem nascer em rascunho. A revisão por IA pode publicar questões como published, nunca reviewed (reservado à revisão humana). Operações destrutivas, arquivamento, desativação, publicação/ativação de cursos, alterações de preço, merges e alterações em massa exigem confirmação explícita do usuário antes da chamada. Nunca invente IDs: pesquise a taxonomia e os registros atuais.';

    protected function boot(): void
    {
        $files = [
            'extractor-tools.json',
            'reviewer-tools.json',
            'reviewer-status-tools.json',
            'taxonomy-tools.json',
            'catalog-tools.json',
            'course-tools.json',
            'marketing-tools.json',
            'engagement-tools.json',
            'google-ads-tools.json',
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

        // A revisão humana é a única autorizada a atribuir o status reviewed.
        $definitions = array_values(array_filter(
            $definitions,
            fn (array $definition): bool => $definition['name'] !== 'finalizeQuestionReview'
        ));

        foreach ($definitions as &$definition) {
            // getQuestion é chamada diretamente pelo MCP, sem route model binding HTTP.
            if ($definition['name'] === 'getQuestion') {
                $definition['bindings']['question'] = [
                    'argument' => 'question',
                    'model' => \App\Models\Question::class,
                ];
            }

            // Corrige searchExams sem alterar a API legada nem outros consumidores.
            if ($definition['name'] === 'searchExams') {
                $definition['controller'] = 'ExamImportLookupApiController';
                $definition['action'] = 'searchExams';
                $definition['description'] = 'Pesquisa concursos pelo título real (exams.title), ano e corporação; inclui contagens de questões por status.';
                $definition['read_only'] = true;
            }
        }
        unset($definition);

        // Consulta conservadora: referências antigas podem não ter exam_id.
        $definitions[] = [
            'name' => 'checkExamImported',
            'title' => 'Verificar prova já importada',
            'description' => 'Verifica indícios de importação pelo ano, banca, órgão e cargo, incluindo referências históricas sem vínculo exam_id. Resultados não comprovam completude ou ausência.',
            'method' => 'GET',
            'path' => '/exams/import-check',
            'controller' => 'ExamImportLookupApiController',
            'action' => 'check',
            'read_only' => true,
            'roles' => ['admin', 'content'],
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'year' => ['type' => 'integer', 'minimum' => 1900, 'maximum' => 2100],
                    'board' => ['type' => 'string', 'minLength' => 2],
                    'organization' => ['type' => 'string', 'minLength' => 2],
                    'position' => ['type' => 'string', 'minLength' => 2],
                ],
                'required' => ['year', 'board', 'organization', 'position'],
            ],
        ];

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
