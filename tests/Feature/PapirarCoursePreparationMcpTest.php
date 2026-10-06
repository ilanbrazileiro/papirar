<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PapirarCoursePreparationMcpTest extends TestCase
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

        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name')->nullable(); $t->string('email')->nullable(); $t->string('password')->nullable(); $t->string('role'); $t->boolean('is_active')->default(true); $t->rememberToken(); $t->timestamps(); });
        Schema::create('corporations', function (Blueprint $t) { $t->id(); $t->string('name')->unique(); $t->string('slug')->unique(); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('subjects', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('slug'); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('topics', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('subject_id'); $t->string('name'); $t->string('slug'); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('exams', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('corporation_id'); $t->string('title'); $t->integer('year'); $t->string('exam_type'); $t->string('status'); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('source_materials', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('corporation_id')->nullable(); $t->unsignedBigInteger('subject_id'); $t->string('title'); $t->string('slug')->unique(); $t->string('material_type'); $t->boolean('active')->default(true); $t->timestamps(); });
        Schema::create('courses', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('corporation_id')->nullable(); $t->unsignedBigInteger('exam_id')->nullable(); $t->string('title'); $t->string('slug')->unique(); $t->string('short_description')->nullable(); $t->text('description')->nullable(); $t->string('course_type')->default('internal_exam');
            $t->decimal('price',10,2)->default(0); $t->decimal('quarterly_price',10,2)->nullable(); $t->decimal('semiannual_price',10,2)->nullable(); $t->boolean('inherit_exam_scope')->default(false); $t->boolean('active')->default(true); $t->boolean('is_public')->default(true); $t->boolean('is_trial_available')->default(true); $t->integer('trial_days')->default(7); $t->integer('sort_order')->default(0); $t->boolean('landing_enabled')->default(false);
            foreach (['sales_headline','sales_badge','target_audience','workload_label','guarantee_text','landing_headline','landing_subheadline','landing_problem_title','landing_problem_text','landing_cta_text','landing_final_title','landing_final_text','landing_final_cta_text','landing_seo_title','landing_seo_description'] as $c) $t->text($c)->nullable();
            $t->json('sales_bullets')->nullable(); $t->unsignedBigInteger('landing_question_id')->nullable();
            foreach (['landing_show_performance','landing_show_error_review','landing_show_next_study','landing_show_schedule','landing_show_goals','landing_show_simulations'] as $c) $t->boolean($c)->default(false);
            $t->timestamps();
        });
        foreach (['course_subjects'=>'subject_id','course_topics'=>'topic_id','course_source_materials'=>'source_material_id'] as $table=>$column) Schema::create($table, function (Blueprint $t) use ($column) { $t->id(); $t->unsignedBigInteger('course_id'); $t->unsignedBigInteger($column); $t->integer('sort_order')->default(0); $t->boolean('is_active')->default(true); $t->timestamps(); });
        Schema::create('course_bundle_items', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('bundle_course_id'); $t->unsignedBigInteger('included_course_id'); $t->timestamps(); });
        Schema::create('course_accesses', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('course_id'); $t->unsignedBigInteger('user_id')->nullable(); $t->timestamps(); });
        Schema::create('subscriptions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('course_id')->nullable(); $t->timestamps(); });

        DB::table('corporations')->insert(['id'=>1,'name'=>'CBMERJ','slug'=>'cbmerj','active'=>1]);
        DB::table('subjects')->insert([['id'=>1,'name'=>'APH','slug'=>'aph','active'=>1],['id'=>2,'name'=>'Incêndio','slug'=>'incendio','active'=>1]]);
        DB::table('topics')->insert([['id'=>1,'subject_id'=>1,'name'=>'Trauma','slug'=>'trauma','active'=>1],['id'=>2,'subject_id'=>2,'name'=>'Mangueiras','slug'=>'mangueiras','active'=>1]]);
        DB::table('exams')->insert(['id'=>1,'corporation_id'=>1,'title'=>'CHOAE 2027','year'=>2027,'exam_type'=>'internal','status'=>'planned','active'=>1]);
        DB::table('source_materials')->insert(['id'=>1,'corporation_id'=>1,'subject_id'=>1,'title'=>'Manual APH','slug'=>'manual-aph','material_type'=>'manual','active'=>1]);
    }

    public function test_course_preparation_tools_expose_safety_metadata(): void
    {
        $this->signIn(); $tools=$this->listAllTools(); $names=array_column($tools,'name');
        foreach (['createCourse','updateCourseMetadata','replaceCourseScope','replaceCourseBundle','updateCoursePricing','updateCourseLanding','setCoursePublication','deleteCourse'] as $name) $this->assertContains($name,$names);
        foreach (['replaceCourseScope','replaceCourseBundle','setCoursePublication','deleteCourse'] as $name) {
            $tool=collect($tools)->firstWhere('name',$name); $this->assertTrue($tool['annotations']['destructiveHint']); $this->assertTrue($tool['_meta']['confirmationRequired']);
        }
        $pricing=collect($tools)->firstWhere('name','updateCoursePricing');
        $this->assertFalse($pricing['annotations']['destructiveHint']);
        $this->assertTrue($pricing['_meta']['confirmationRequired']);
    }

    public function test_create_course_is_safe_by_default(): void
    {
        $this->signIn();
        $data=$this->toolData($this->callTool('createCourse',['corporation_id'=>1,'exam_id'=>1,'title'=>'CHOAE Preparatório','course_type'=>'internal_exam','inherit_exam_scope'=>true]));
        $this->assertSame(201,$data['http_status']);
        $this->assertDatabaseHas('courses',['title'=>'CHOAE Preparatório','active'=>0,'is_public'=>0,'landing_enabled'=>0,'price'=>0]);
    }

    public function test_own_scope_requires_confirmation_and_valid_taxonomy(): void
    {
        $this->signIn(); $course=$this->createCourse(false);
        $bad=$this->callTool('replaceCourseScope',['course'=>$course,'confirm'=>false,'reason'=>'Teste de segurança sem confirmação.','subjects'=>[['subject_id'=>1,'topic_ids'=>[1]]],'source_material_ids'=>[1]])->assertJsonPath('result.isError',true);
        $this->assertSame(422,$this->toolData($bad)['http_status']);
        $ok=$this->toolData($this->callTool('replaceCourseScope',['course'=>$course,'confirm'=>true,'reason'=>'Escopo próprio confirmado para o curso.','subjects'=>[['subject_id'=>1,'topic_ids'=>[1]]],'source_material_ids'=>[1]]));
        $this->assertSame(200,$ok['http_status']); $this->assertDatabaseHas('course_subjects',['course_id'=>$course,'subject_id'=>1]); $this->assertDatabaseHas('course_topics',['course_id'=>$course,'topic_id'=>1]);
        $wrong=$this->callTool('replaceCourseScope',['course'=>$course,'confirm'=>true,'reason'=>'Validação de tópico fora da disciplina.','subjects'=>[['subject_id'=>1,'topic_ids'=>[2]]]])->assertJsonPath('result.isError',true);
        $this->assertSame(422,$this->toolData($wrong)['http_status']);
    }

    public function test_pricing_and_publication_require_confirmation(): void
    {
        $this->signIn(); $course=$this->createCourse(false);
        $this->callTool('updateCoursePricing',['course'=>$course,'confirm'=>false,'reason'=>'Tentativa sem confirmação de preço.','price'=>49.90])->assertJsonPath('result.isError',true);
        $ok=$this->toolData($this->callTool('updateCoursePricing',['course'=>$course,'confirm'=>true,'reason'=>'Preço aprovado para disponibilização do curso.','price'=>49.90,'trial_days'=>7]));
        $this->assertSame(200,$ok['http_status']);
        $this->callTool('setCoursePublication',['course'=>$course,'confirm'=>false,'reason'=>'Tentativa sem confirmação de publicação.','active'=>true,'is_public'=>true,'landing_enabled'=>true])->assertJsonPath('result.isError',true);
        $published=$this->toolData($this->callTool('setCoursePublication',['course'=>$course,'confirm'=>true,'reason'=>'Publicação do curso aprovada para teste.','active'=>true,'is_public'=>true,'landing_enabled'=>true]));
        $this->assertSame(200,$published['http_status']); $this->assertDatabaseHas('courses',['id'=>$course,'active'=>1,'is_public'=>1,'landing_enabled'=>1]);
    }

    public function test_delete_course_is_confirmed_and_blocked_when_access_exists(): void
    {
        $this->signIn(); $course=$this->createCourse(false);
        DB::table('course_accesses')->insert(['course_id'=>$course,'created_at'=>now(),'updated_at'=>now()]);
        $blocked=$this->callTool('deleteCourse',['course'=>$course,'confirm'=>true,'reason'=>'Teste de proteção de curso com acesso.'])->assertJsonPath('result.isError',true);
        $this->assertSame(422,$this->toolData($blocked)['http_status']); $this->assertDatabaseHas('courses',['id'=>$course]);
        DB::table('course_accesses')->where('course_id',$course)->delete();
        $deleted=$this->toolData($this->callTool('deleteCourse',['course'=>$course,'confirm'=>true,'reason'=>'Exclusão confirmada de curso sem vínculos.']));
        $this->assertSame(200,$deleted['http_status']); $this->assertDatabaseMissing('courses',['id'=>$course]);
    }

    private function createCourse(bool $inherit): int
    {
        $args=['corporation_id'=>1,'title'=>'Curso '.uniqid(),'course_type'=>'internal_exam','inherit_exam_scope'=>$inherit]; if ($inherit) $args['exam_id']=1;
        $data=$this->toolData($this->callTool('createCourse',$args)); return (int)$data['response']['data']['id'];
    }
    private function signIn(): void { Passport::actingAs(new User(['role'=>'admin','is_active'=>true]),['mcp:use'],'api'); }
    private function listAllTools(): array { $tools=[];$cursor=null;$seen=[]; do { $r=$this->rpc('tools/list',$cursor?['cursor'=>$cursor]:[])->assertOk(); $tools=array_merge($tools,$r->json('result.tools')??[]); $next=$r->json('result.nextCursor'); if(!$next||in_array($next,$seen,true)) break; $seen[]=$next;$cursor=$next; } while(count($seen)<30); return $tools; }
    private function rpc(string $method,array $params=[]) { $headers=['Accept'=>'application/json, text/event-stream','MCP-Protocol-Version'=>'2025-06-18','Origin'=>'https://chatgpt.com']; if($method==='tools/call')$headers['Mcp-Name']=$params['name']; return $this->postJson('/mcp/papirar',['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>(object)$params],$headers); }
    private function callTool(string $name,array $arguments=[]) { return $this->rpc('tools/call',['name'=>$name,'arguments'=>(object)$arguments])->assertOk(); }
    private function toolData($response): array { return json_decode($response->json('result.content.0.text'),true,512,JSON_THROW_ON_ERROR); }
}
