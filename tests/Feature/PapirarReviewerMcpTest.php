<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PapirarReviewerMcpTest extends TestCase
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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('scope')->default('general');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            foreach (['corporation_id', 'exam_id', 'exam_board_id', 'source_material_id', 'created_by'] as $column) {
                $table->unsignedBigInteger($column)->nullable();
            }
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('topic_id');
            foreach (['statement', 'question_type', 'difficulty', 'source_type', 'status'] as $column) {
                $table->text($column);
            }
            $table->text('source_reference')->nullable();
            $table->text('commented_answer')->nullable();
            $table->timestamps();
        });
        Schema::create('alternatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('letter');
            $table->text('text');
            $table->boolean('is_correct');
            $table->timestamps();
        });
        Schema::create('question_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->timestamps();
        });

        DB::table('subjects')->insert(['id' => 1, 'name' => 'Português', 'slug' => 'portugues']);
        DB::table('topics')->insert(['id' => 1, 'subject_id' => 1, 'name' => 'Crase', 'slug' => 'crase']);
    }

    public function test_unified_server_lists_reviewer_and_taxonomy_tools_with_safety_metadata(): void
    {
        $this->signIn();
        $tools = $this->rpc('tools/list')->assertOk()->json('result.tools');
        $names = array_column($tools, 'name');

        foreach (['updateQuestionContent', 'finalizeQuestionReview', 'archiveQuestion', 'reviewTaxonomy', 'updateQuestionClassification', 'moveTopic', 'mergeTopic', 'mergeSubject'] as $name) {
            $this->assertContains($name, $names);
        }

        $archive = collect($tools)->firstWhere('name', 'archiveQuestion');
        $this->assertTrue($archive['annotations']['destructiveHint']);
        $this->assertTrue($archive['_meta']['confirmationRequired']);

        $merge = collect($tools)->firstWhere('name', 'mergeTopic');
        $this->assertTrue($merge['annotations']['destructiveHint']);
        $this->assertTrue($merge['_meta']['confirmationRequired']);
    }

    public function test_content_role_cannot_use_admin_only_structural_merge(): void
    {
        Passport::actingAs(new User(['role' => 'content', 'is_active' => true]), ['mcp:use'], 'api');
        $response = $this->callTool('mergeTopic', [
            'sourceTopic' => 1,
            'target_topic_id' => 2,
            'confirm' => true,
            'reason' => 'Teste de autorização administrativa.',
        ]);

        $response->assertJsonPath('result.isError', true);
    }

    public function test_reviewer_updates_alternatives_and_correct_answer_without_changing_status(): void
    {
        $this->signIn();
        $question = $this->createQuestion();

        $data = $this->toolData($this->callTool('updateQuestionContent', [
            'question' => $question->id,
            'statement' => 'Qual alternativa apresenta corretamente o emprego da crase?',
            'difficulty' => 'medium',
            'commented_answer' => str_repeat('Comentário didático para validar a revisão editorial da questão. ', 2),
            'correct_letter' => 'B',
            'alternatives' => [
                ['letter' => 'A', 'text' => 'Fui a escola.'],
                ['letter' => 'B', 'text' => 'Fui à escola.'],
            ],
            'reason' => 'Correção editorial do enunciado, alternativas e gabarito.',
        ]));

        $this->assertSame(200, $data['http_status']);
        $this->assertSame('draft', $data['response']['data']['status']);
        $this->assertSame('B', $data['response']['data']['correct_letter']);
        $this->assertDatabaseHas('alternatives', ['question_id' => $question->id, 'letter' => 'B', 'is_correct' => 1]);
        $this->assertDatabaseHas('questions', ['id' => $question->id, 'difficulty' => 'medium', 'status' => 'draft']);
    }

    public function test_finalize_requires_editorial_requirements_and_marks_valid_question_reviewed(): void
    {
        $this->signIn();
        $invalid = $this->createQuestion(commentedAnswer: null);
        $failed = $this->callTool('finalizeQuestionReview', [
            'question' => $invalid->id,
            'reason' => 'Tentativa controlada sem comentário suficiente.',
        ])->assertJsonPath('result.isError', true);
        $this->assertSame(422, $this->toolData($failed)['http_status']);
        $this->assertDatabaseHas('questions', ['id' => $invalid->id, 'status' => 'draft']);

        $valid = $this->createQuestion(
            statement: 'Segunda questão válida para revisão final.',
            commentedAnswer: str_repeat('Explicação didática suficiente para a validação editorial final. ', 2),
        );
        $data = $this->toolData($this->callTool('finalizeQuestionReview', [
            'question' => $valid->id,
            'reason' => 'Conteúdo, gabarito, comentário e classificação conferidos.',
        ]));

        $this->assertSame(200, $data['http_status']);
        $this->assertSame(Question::STATUS_REVIEWED, $data['response']['data']['status']);
        $this->assertDatabaseHas('questions', ['id' => $valid->id, 'status' => Question::STATUS_REVIEWED]);
    }

    public function test_archive_requires_confirmation_and_archived_question_cannot_be_edited(): void
    {
        $this->signIn();
        $question = $this->createQuestion();

        $this->callTool('archiveQuestion', [
            'question' => $question->id,
            'confirm' => false,
            'reason' => 'Teste de proteção contra arquivamento sem confirmação.',
        ])->assertJsonPath('result.isError', true);
        $this->assertDatabaseHas('questions', ['id' => $question->id, 'status' => 'draft']);

        $data = $this->toolData($this->callTool('archiveQuestion', [
            'question' => $question->id,
            'confirm' => true,
            'reason' => 'Arquivamento confirmado para teste de segurança.',
        ]));
        $this->assertSame(200, $data['http_status']);
        $this->assertDatabaseHas('questions', ['id' => $question->id, 'status' => Question::STATUS_ARCHIVED]);

        $edit = $this->callTool('updateQuestionContent', [
            'question' => $question->id,
            'statement' => 'Tentativa de alteração posterior ao arquivamento.',
            'reason' => 'Teste de bloqueio de edição em questão arquivada.',
        ])->assertJsonPath('result.isError', true);
        $this->assertSame(409, $this->toolData($edit)['http_status']);
    }

    public function test_reviewer_rejects_duplicate_statement(): void
    {
        $this->signIn();
        $first = $this->createQuestion('Enunciado já existente para teste de duplicidade.');
        $second = $this->createQuestion('Outro enunciado antes da tentativa de revisão.');

        $response = $this->callTool('updateQuestionContent', [
            'question' => $second->id,
            'statement' => $first->statement,
            'reason' => 'Teste de bloqueio de enunciado duplicado durante revisão.',
        ])->assertJsonPath('result.isError', true);

        $this->assertSame(409, $this->toolData($response)['http_status']);
        $this->assertDatabaseHas('questions', ['id' => $second->id, 'statement' => 'Outro enunciado antes da tentativa de revisão.']);
    }

    private function createQuestion(
        string $statement = 'Questão original para revisão editorial.',
        ?string $commentedAnswer = null,
    ): Question {
        $question = Question::create([
            'subject_id' => 1,
            'topic_id' => 1,
            'statement' => $statement,
            'question_type' => 'multiple_choice',
            'difficulty' => 'easy',
            'source_type' => 'authored',
            'status' => Question::STATUS_DRAFT,
            'commented_answer' => $commentedAnswer,
        ]);
        $question->alternatives()->createMany([
            ['letter' => 'A', 'text' => 'Alternativa correta inicial.', 'is_correct' => true],
            ['letter' => 'B', 'text' => 'Alternativa incorreta inicial.', 'is_correct' => false],
        ]);

        return $question;
    }

    private function signIn(): void
    {
        Passport::actingAs(new User(['role' => 'admin', 'is_active' => true]), ['mcp:use'], 'api');
    }

    private function rpc(string $method, array $params = [])
    {
        $headers = [
            'Accept' => 'application/json, text/event-stream',
            'MCP-Protocol-Version' => '2025-06-18',
            'Origin' => 'https://chatgpt.com',
        ];
        if ($method === 'tools/call') {
            $headers['Mcp-Name'] = $params['name'];
        }

        return $this->postJson('/mcp/papirar', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => (object) $params,
        ], $headers);
    }

    private function callTool(string $name, array $arguments = [])
    {
        return $this->rpc('tools/call', ['name' => $name, 'arguments' => (object) $arguments])->assertOk();
    }

    private function toolData($response): array
    {
        return json_decode($response->json('result.content.0.text'), true, 512, JSON_THROW_ON_ERROR);
    }
}
