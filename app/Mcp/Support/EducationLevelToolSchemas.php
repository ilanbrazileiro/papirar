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

        return $definitions;
    }
}
