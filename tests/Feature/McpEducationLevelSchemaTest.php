<?php

namespace Tests\Feature;

use App\Mcp\Support\EducationLevelToolSchemas;
use Tests\TestCase;

class McpEducationLevelSchemaTest extends TestCase
{
    public function test_extractor_create_and_search_tools_expose_education_level(): void
    {
        $tools = EducationLevelToolSchemas::apply($this->definitions('extractor-tools.json'));

        foreach (['createDraftQuestion', 'createDraftQuestionsBatch'] as $name) {
            $tool = $this->findTool($tools, $name);
            $properties = $name === 'createDraftQuestionsBatch'
                ? $tool['schema']['properties']['questions']['items']['properties']
                : $tool['schema']['properties'];
            $this->assertSame(['medium', 'higher'], $properties['education_level']['enum']);
            $this->assertNotContains('education_level', $tool['schema']['required'] ?? []);
        }

        $search = $this->findTool($tools, 'searchQuestions');
        $this->assertSame(['medium', 'higher', 'unclassified'], $search['schema']['properties']['education_level']['enum']);
    }

    public function test_unified_reviewer_exposes_education_level_and_keeps_other_fields(): void
    {
        $tools = EducationLevelToolSchemas::apply(array_merge(
            $this->definitions('extractor-tools.json'),
            $this->definitions('reviewer-tools.json')
        ));

        $review = $this->findTool($tools, 'updateQuestionContent');
        $this->assertSame(['medium', 'higher'], $review['schema']['properties']['education_level']['enum']);
        $this->assertContains('reason', $review['schema']['required']);
        $this->assertArrayHasKey('alternatives', $review['schema']['properties']);
        $this->assertArrayHasKey('question', $review['bindings']);
    }

    public function test_individual_education_tool_uses_existing_endpoint_and_model_binding(): void
    {
        $tools = EducationLevelToolSchemas::apply(array_merge(
            $this->definitions('extractor-tools.json'),
            $this->definitions('reviewer-tools.json')
        ));

        $tool = $this->findTool($tools, 'updateQuestionEducationLevel');
        $this->assertSame('PATCH', $tool['method']);
        $this->assertSame('/questions/{question}/education-level', $tool['path']);
        $this->assertSame('EducationLevelApiController', $tool['controller']);
        $this->assertSame('updateQuestion', $tool['action']);
        $this->assertSame(\App\Models\Question::class, $tool['bindings']['question']['model']);
        $this->assertSame(['question', 'education_level', 'reason'], $tool['schema']['required']);
        $this->assertSame(['medium', 'higher'], $tool['schema']['properties']['education_level']['enum']);
        $this->assertSame(10, $tool['schema']['properties']['reason']['minLength']);
        $this->assertSame(['admin', 'content'], $tool['roles']);
        $this->assertFalse($tool['read_only']);
        $this->assertFalse($tool['destructive']);
    }

    public function test_invalid_level_is_not_in_advertised_schema_and_application_is_idempotent(): void
    {
        $first = EducationLevelToolSchemas::apply($this->definitions('extractor-tools.json'));
        $second = EducationLevelToolSchemas::apply($first);
        $this->assertSame($first, $second);
        $create = $this->findTool($first, 'createDraftQuestion');
        $this->assertNotContains('unclassified', $create['schema']['properties']['education_level']['enum']);
        $this->assertCount(1, array_filter($second, fn (array $tool) => $tool['name'] === 'updateQuestionEducationLevel'));
    }

    private function definitions(string $file): array
    {
        return json_decode(file_get_contents(resource_path('mcp/'.$file)), true, 512, JSON_THROW_ON_ERROR);
    }

    private function findTool(array $definitions, string $name): array
    {
        foreach ($definitions as $definition) {
            if ($definition['name'] === $name) {
                return $definition;
            }
        }

        $this->fail("Ferramenta MCP não encontrada: {$name}");
    }
}
