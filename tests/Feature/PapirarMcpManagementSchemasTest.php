<?php

namespace Tests\Feature;

use App\Mcp\Support\CatalogEducationToolSchemas;
use App\Mcp\Support\EducationLevelToolSchemas;
use Tests\TestCase;

class PapirarMcpManagementSchemasTest extends TestCase
{
    public function test_all_management_domains_and_reviewer_tools_are_available(): void
    {
        $files = [
            'extractor-tools.json', 'reviewer-tools.json', 'reviewer-status-tools.json',
            'taxonomy-tools.json', 'catalog-tools.json', 'course-tools.json',
            'catalog-maintenance-tools.json',
        ];
        $tools = [];
        foreach ($files as $file) {
            $tools = array_merge($tools, json_decode(file_get_contents(resource_path('mcp/'.$file)), true, 512, JSON_THROW_ON_ERROR));
        }
        $tools = CatalogEducationToolSchemas::apply(EducationLevelToolSchemas::apply($tools));
        $names = array_column($tools, 'name');

        $this->assertCount(count(array_unique($names)), $names);
        foreach ([
            'searchQuestions', 'getQuestion', 'createDraftQuestion', 'createDraftQuestionsBatch',
            'updateQuestionContent', 'publishQuestion', 'returnQuestionToDraft',
            'updateQuestionClassification', 'updateQuestionEducationLevel',
            'searchSubjects', 'createSubject', 'updateSubject',
            'searchTopics', 'createTopic', 'updateTopic',
            'searchExams', 'createExam', 'updateExam', 'updateExamEducationLevel',
            'searchCourses', 'getCourse', 'getCourseCoverage', 'createCourse',
            'updateCourseMetadata', 'updateCourseEducationLevel', 'replaceCourseScope',
        ] as $name) {
            $this->assertContains($name, $names);
        }

        foreach ([
            'createExam' => 'storeExam', 'updateExam' => 'updateExam',
            'createCourse' => 'storeCourse', 'updateCourseMetadata' => 'updateCourseMetadata',
            'searchCourses' => 'courses',
        ] as $name => $method) {
            $tool = collect($tools)->firstWhere('name', $name);
            $this->assertSame('EducationCatalogApiController', $tool['controller']);
            $this->assertSame($method, $tool['action']);
            $this->assertSame(
                $name === 'searchCourses' ? ['medium', 'higher', 'unclassified'] : ['medium', 'higher'],
                $tool['schema']['properties']['education_level']['enum']
            );
        }

        foreach (['updateSubject' => 'subject', 'updateTopic' => 'topic'] as $name => $binding) {
            $tool = collect($tools)->firstWhere('name', $name);
            $this->assertSame('TaxonomyMaintenanceApiController', $tool['controller']);
            $this->assertSame([$binding, 'reason'], $tool['schema']['required']);
            $this->assertArrayHasKey($binding, $tool['bindings']);
        }
    }
}
