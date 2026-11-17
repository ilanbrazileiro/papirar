<?php

namespace App\Mcp\Support;

/**
 * Faz o MCP de cursos e concursos usar os controllers que aceitam escolaridade
 * independente. Não altera os endpoints HTTP legados.
 */
final class CatalogEducationToolSchemas
{
    public static function apply(array $definitions): array
    {
        $actions = [
            'createExam' => 'storeExam',
            'updateExam' => 'updateExam',
            'createCourse' => 'storeCourse',
            'updateCourseMetadata' => 'updateCourseMetadata',
        ];

        foreach ($definitions as &$definition) {
            $name = $definition['name'] ?? '';
            if (isset($actions[$name])) {
                $definition['controller'] = 'EducationCatalogApiController';
                $definition['action'] = $actions[$name];
                $definition['schema']['properties']['education_level'] = [
                    'type' => 'string',
                    'enum' => ['medium', 'higher'],
                    'description' => 'Nível do próprio curso/concurso, independente das questões e do cadastro relacionado.',
                ];
            }
            if ($name === 'searchCourses') {
                $definition['controller'] = 'EducationCatalogApiController';
                $definition['action'] = 'courses';
                $definition['schema']['properties']['education_level'] = [
                    'type' => 'string',
                    'enum' => ['medium', 'higher', 'unclassified'],
                ];
            }
        }
        unset($definition);

        return $definitions;
    }
}
