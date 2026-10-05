<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PapirarCatalogCourseMcpTest extends TestCase
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

        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('email'); $t->string('password'); $t->string('role'); $t->boolean('is_active')->default(true); $t->rememberToken(); $t->timestamps(); });
        Schema::create('corporations', function (Blueprint $t) { $t->id(); $t->string('name')->unique(); $t->string('slug')->unique(); $t->text('description')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('exam_boards', function (Blueprint $t) { $t->id(); $t->string('name')->unique(); $t->string('slug')->unique(); $t->text('description')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('subjects', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('slug'); $t->string('scope')->default('general'); $t->text('description')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('topics', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('subject_id'); $t->string('name'); $t->string('slug'); $t->text('description')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('exams', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('corporation_id'); $t->string('title'); $t->integer('year'); $t->string('exam_type'); $t->string('status'); $t->text('description')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('exam_subjects', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('exam_id'); $t->unsignedBigInteger('subject_id'); $t->integer('sort_order')->default(0); $t->boolean('is_active')->default(true); $t->timestamps(); });
        Schema::create('exam_subject_topics', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('exam_subject_id'); $t->unsignedBigInteger('topic_id'); $t->integer('sort_order')->default(0); $t->boolean('is_active')->default(true); $t->timestamps(); });
        Schema::create('source_materials', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('corporation_id')->nullable(); $t->unsignedBigInteger('subject_id'); $t->string('title'); $t->string('slug')->unique(); $t->text('description')->nullable(); $t->string('material_type'); $t->integer('year')->nullable(); $t->string('reference_code')->nullable(); $t->string('url')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('exam_subject_source_materials', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('exam_subject_id'); $t->unsignedBigInteger('source_material_id'); $t->boolean('is_active')->default(true); $t->timestamps(); });
        Schema::create('courses', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('corporation_id')->nullable(); $t->unsignedBigInteger('exam_id')->nullable(); $t->string('title'); $t->string('slug'); $t->string('course_type')->default('internal_exam'); $t->decimal('price', 10, 2)->default(0); $t->boolean('inherit_exam_scope')->default(false); $t->boolean('active')->default(true); $t->boolean('is_public')->default(true); $t->boolean('is_trial_available')->default(true); $t->integer('trial_days')->default(7); $t->integer('sort_order')->default(0); $t->boolean('landing_enabled')->default(false); $t->timestamps(); });
        foreach (['course_subjects' => 'subject_id', 'course_topics' => 'topic_id', 'course_source_materials' => 'source_material_id'] as $table => $column) {
            Schema::create($table, function (Blueprint $t) use ($column) { $t->id(); $t->unsignedBigInteger('course_id'); $t->unsignedBigInteger($column); $t->integer('sort_order')->default(0); $t->boolean('is_active')->default(true); $t->timestamps(); });
        }
        Schema::create('course_bundle_items', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('bundle_course_id'); $t->unsignedBigInteger('included_course_id'); $t->timestamps(); });
        Schema::create('questions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('subject_id'); $t->unsignedBigInteger('topic_id'); $t->unsignedBigInteger('source_material_id')->nullable(); $t->text('statement'); $t->string('question_type')->default('multiple_choice'); $t->string('difficulty')->default('medium'); $t->string('source_type')->default('authored'); $t->string('status'); $t->timestamps(); });

        DB::table('corporations')->insert(['id'=>1,'name'=>'CBMERJ','slug'=>'cbmerj','active'=>1]);
        DB::table('subjects')->insert(['id'=>1,'name'=>'APH','slug'=>'aph','active'=>1]);
        DB::table('topics')->insert(['id'=>1,'subject_id'=>1,'name'=>'Trauma','slug'=>'trauma','active'=>1]);
    }

    public function test_catalog_and_course_tools_are_exposed_with_scope_safety(): void
    {
        $this->signIn();
        $tools = $this->listAllTools();
        $names = array_column($tools, 'name');
        foreach (['createCorporation','createExamBoard','createSourceMaterial','createExam','replaceExamScope','searchCourses','getCourse','getCourseCoverage'] as $name) {
            $this->assertContains($name, $names);
        }
        $scope = collect($tools)->firstWhere('name', 'replaceExamScope');
        $this->assertTrue($scope['annotations']['destructiveHint']);
        $this->assertTrue($scope['_meta']['confirmationRequired']);
    }

    public function test_content_role_cannot_create_corporation(): void
    {
        Passport::actingAs(new User(['role'=>'content','is_active'=>true]), ['mcp:use'], 'api');
        $this->callTool('createCorporation', ['name'=>'PMERJ'])->assertJsonPath('result.isError', true);
    }

    public function test_admin_can_create_source_material_and_exam_with_scope(): void
    {
        $this->signIn();
        $material = $this->toolData($this->callTool('createSourceMaterial', ['corporation_id'=>1,'subject_id'=>1,'title'=>'Manual APH','material_type'=>'manual']));
        $this->assertSame(201, $material['http_status']);
        $this->assertDatabaseHas('source_materials', ['title'=>'Manual APH','subject_id'=>1]);

        $exam = $this->toolData($this->callTool('createExam', [
            'corporation_id'=>1,'title'=>'CHOAE 2027','year'=>2027,'exam_type'=>'internal','status'=>'planned',
            'scope'=>[['subject_id'=>1,'topic_ids'=>[1]]],
        ]));
        $this->assertSame(201, $exam['http_status']);
        $this->assertDatabaseHas('exams', ['title'=>'CHOAE 2027','status'=>'planned']);
        $this->assertDatabaseHas('exam_subject_topics', ['topic_id'=>1]);
    }

    public function test_replace_exam_scope_requires_confirmation(): void
    {
        $this->signIn();
        DB::table('exams')->insert(['id'=>1,'corporation_id'=>1,'title'=>'Teste','year'=>2027,'exam_type'=>'internal','status'=>'planned','active'=>1]);
        $failed = $this->callTool('replaceExamScope', ['exam'=>1,'confirm'=>false,'reason'=>'Teste sem confirmação explícita.','subjects'=>[['subject_id'=>1,'topic_ids'=>[1]]]])->assertJsonPath('result.isError', true);
        $this->assertSame(422, $this->toolData($failed)['http_status']);
        $ok = $this->toolData($this->callTool('replaceExamScope', ['exam'=>1,'confirm'=>true,'reason'=>'Substituição confirmada para teste.','subjects'=>[['subject_id'=>1,'topic_ids'=>[1]]]]));
        $this->assertSame(200, $ok['http_status']);
    }

    private function signIn(): void { Passport::actingAs(new User(['role'=>'admin','is_active'=>true]), ['mcp:use'], 'api'); }

    private function listAllTools(): array
    {
        $tools=[]; $cursor=null; $seen=[];
        do {
            $response=$this->rpc('tools/list', $cursor ? ['cursor'=>$cursor] : [])->assertOk();
            $tools=array_merge($tools, $response->json('result.tools') ?? []);
            $next=$response->json('result.nextCursor');
            if (!$next || in_array($next,$seen,true)) break;
            $seen[]=$next; $cursor=$next;
        } while (count($seen)<30);
        return $tools;
    }

    private function rpc(string $method, array $params=[])
    {
        $headers=['Accept'=>'application/json, text/event-stream','MCP-Protocol-Version'=>'2025-06-18','Origin'=>'https://chatgpt.com'];
        if ($method==='tools/call') $headers['Mcp-Name']=$params['name'];
        return $this->postJson('/mcp/papirar', ['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>(object)$params], $headers);
    }
    private function callTool(string $name, array $arguments=[]) { return $this->rpc('tools/call', ['name'=>$name,'arguments'=>(object)$arguments])->assertOk(); }
    private function toolData($response): array { return json_decode($response->json('result.content.0.text'), true, 512, JSON_THROW_ON_ERROR); }
}
