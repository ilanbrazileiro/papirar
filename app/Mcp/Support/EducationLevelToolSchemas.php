<?php

namespace App\Mcp\Support;

/**
 * Expõe a escolaridade nas ferramentas MCP sem alterar o contrato HTTP existente.
 * A classificação permanece opcional no cadastro por compatibilidade histórica.
 */
final class EducationLevelToolSchemas
{
    public static function apply(array $definitions): array
    {
        foreach ($definitions as &$definition) {
            $name = $definition['name'] ?? null;

            if (in_array($name, ['createDraftQuestion', 'createDraftQuestionsBatch', 'updateQuestionContent'], true)) {
                $properties = &$definition['schema']['properties'];

                if ($name === 'createDraftQuestionsBatch') {
                    $properties = &$definition['schema']['properties']['questions']['items']['properties'];
                }

                // A API aceita somente medium e higher. Não atribuir nível por presunção.
                $properties['education_level'] = [
                    'type' => 'string',
                    'enum' => ['medium', 'higher'],
                    'description' => 'Escolaridade da questão: medium (médio) ou higher (superior). Informe somente após verificar a prova e seu nível.',
                ];
                unset($properties);
            }

            if ($name === 'searchQuestions') {
                $definition['schema']['properties']['education_level'] = [
                    'type' => 'string',
                    'enum' => ['medium', 'higher', 'unclassified'],
                    'description' => 'Filtra por médio, superior ou questões sem escolaridade (unclassified).',
                ];
            }
        }
        unset($definition);

        // A API GPT já possui esta ação. O MCP precisa anunciar a ferramenta
        // individualmente para permitir corrigir só a escolaridade, sem editar
        // enunciado, alternativas, gabarito ou status.
        if (! in_array('updateQuestionEducationLevel', array_column($definitions, 'name'), true)) {
            $definitions[] = [
                'name' => 'updateQuestionEducationLevel',
                'title' => 'Altera escolaridade da questão',
                'description' => 'Altera exclusivamente a escolaridade de uma questão não arquivada. Consulte a questão e confirme o nível da prova antes de chamar. Exige justificativa verificável. Não altera gabarito, conteúdo ou status.',
                'method' => 'PATCH',
                'path' => '/questions/{question}/education-level',
                'controller' => 'EducationLevelApiController',
                'action' => 'updateQuestion',
                'read_only' => false,
                'destructive' => false,
                'idempotent' => true,
                'roles' => ['admin', 'content'],
                'bindings' => [
                    'question' => [
                        'argument' => 'question',
                        'model' => \App\Models\Question::class,
                    ],
                ],
                'schema' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['question', 'education_level', 'reason'],
                    'properties' => [
                        'question' => ['type' => 'integer', 'minimum' => 1],
                        'education_level' => [
                            'type' => 'string',
                            'enum' => ['medium', 'higher'],
                        ],
                        'reason' => [
                            'type' => 'string',
                            'minLength' => 10,
                            'maxLength' => 1000,
                        ],
                    ],
                ],
            ];
        }

        return $definitions;
    }
}
