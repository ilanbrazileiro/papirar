<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ExtractorMcpTest extends TestCase
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

        // Existing application migrations contain MySQL-specific DDL. These
        // fixtures exercise the real controllers against an isolated SQLite DB.
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

    public function test_authentication_discovery_and_login_redirect(): void
    {
        $this->rpc('initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => [], 'clientInfo' => ['name' => 'test', 'version' => '1']])
            ->assertUnauthorized()->assertHeader('WWW-Authenticate');
        $this->getJson('/.well-known/oauth-authorization-server')
            ->assertOk()->assertJsonPath('code_challenge_methods_supported.0', 'S256');
        $this->getJson('/.well-known/oauth-protected-resource/mcp/extractor')
            ->assertOk()->assertJsonPath('scopes_supported.0', 'mcp:use');
    }

    public function test_only_active_content_accounts_with_mcp_scope_can_access(): void
    {
        foreach ([['student', true, ['mcp:use']], ['marketing', true, ['mcp:use']], ['admin', false, ['mcp:use']], ['admin', true, []]] as [$role, $active, $scopes]) {
            Passport::actingAs(new User(['role' => $role, 'is_active' => $active]), $scopes, 'api');
            $this->rpc('tools/list')->assertForbidden();
        }
        $this->signIn();
        $this->withHeader('Origin', 'https://example.org')->rpc('tools/list')->assertForbidden();
        $this->withHeader('Origin', 'https://chatgpt.com');
        $response = $this->rpc('tools/list')->assertOk();
        $tools = $response->json('result.tools');
        $this->assertCount(15, $tools);
        $this->assertNotContains('reviewAndPublish', array_column($tools, 'name'));
        foreach ($tools as $tool) {
            $this->assertSame('oauth2', $tool['securitySchemes'][0]['type']);
            $this->assertSame(['mcp:use'], $tool['securitySchemes'][0]['scopes']);
        }
        $this->callTool('checkPapirarApi')->assertJsonPath('result.isError', false);
    }

    public function test_creates_draft_reads_back_and_blocks_duplicate(): void
    {
        $this->signIn();
        $created = $this->toolData($this->callTool('createDraftQuestion', $this->question()));
        $this->assertSame(201, $created['http_status']);
        $id = $created['response']['data']['id'];
        $this->assertDatabaseHas('questions', ['id' => $id, 'status' => 'draft']);
        $read = $this->toolData($this->callTool('getQuestion', ['question' => $id]));
        $this->assertSame($this->question()['statement'], $read['response']['data']['statement']);
        $this->assertCount(2, $read['response']['data']['alternatives']);
        $duplicate = $this->callTool('createDraftQuestion', $this->question())->assertJsonPath('result.isError', true);
        $this->assertSame(409, $this->toolData($duplicate)['http_status']);
        $this->assertDatabaseCount('questions', 1);
    }

    public function test_partial_batch_keeps_created_duplicate_and_error_results(): void
    {
        $this->signIn();
        $this->callTool('createDraftQuestion', $this->question());
        $response = $this->callTool('createDraftQuestionsBatch', ['questions' => [
            $this->question('Enunciado novo para o lote.'),
            $this->question(),
            array_replace($this->question('Tópico incompatível para validar.'), ['topic_id' => 999]),
        ]]);
        $data = $this->toolData($response)['response'];
        $this->assertSame(['received' => 3, 'created' => 1, 'duplicates' => 1, 'errors' => 1], $data['summary']);
        $this->assertSame(['created', 'duplicate', 'validation_error'], array_column($data['results'], 'status'));
        $this->assertDatabaseCount('questions', 2);
    }

    public function test_rejects_unknown_fields_large_batch_and_invalid_correct_letter(): void
    {
        $this->signIn();
        foreach ([array_replace($this->question(), ['status' => 'published']), array_replace($this->question(), ['correct_letter' => 'E'])] as $invalid) {
            $this->callTool('createDraftQuestion', $invalid)->assertJsonPath('result.isError', true);
        }
        $this->callTool('createDraftQuestionsBatch', ['questions' => array_fill(0, 21, $this->question())])
            ->assertJsonPath('result.isError', true);
        $this->assertDatabaseCount('questions', 0);
    }

    public function test_taxonomy_creation_and_question_pagination(): void
    {
        $this->signIn();
        $subject = $this->toolData($this->callTool('createSubject', ['name' => 'Matemática']))['response']['data'];
        $topic = $this->toolData($this->callTool('createTopic', ['subject_id' => $subject['id'], 'name' => 'Frações']))['response']['data'];
        $this->assertSame($subject['id'], $topic['subject_id']);
        $this->callTool('createDraftQuestion', $this->question('Primeira questão para paginação.'));
        $this->callTool('createDraftQuestion', $this->question('Segunda questão para paginação.'));
        $page = $this->toolData($this->callTool('searchQuestions', ['per_page' => 1, 'page' => 2]))['response'];
        $this->assertSame(2, $page['meta']['current_page']);
        $this->assertCount(1, $page['data']);
    }

    public function test_oauth_pkce_authorization_token_exchange_and_authenticated_mcp(): void
    {
        foreach (glob(database_path('migrations/*create_oauth_*.php')) as $path) {
            (require $path)->up();
        }
        $callback = 'https://chatgpt.com/connector/oauth/test-papirar';
        $registered = $this->postJson('/oauth/register', ['client_name' => 'Teste Papirar', 'redirect_uris' => [$callback]])->assertCreated();
        $clientId = $registered->json('client_id');
        $this->postJson('/oauth/register', ['redirect_uris' => ['https://example.org/callback']])->assertStatus(400);
        $verifier = str_repeat('a', 64);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $query = ['client_id' => $clientId, 'redirect_uri' => $callback, 'response_type' => 'code', 'scope' => 'mcp:use', 'state' => 'test-state', 'code_challenge' => $challenge, 'code_challenge_method' => 'S256', 'resource' => url('/mcp/extractor')];
        $authorizeUrl = '/oauth/authorize?'.http_build_query($query);
        $this->get($authorizeUrl)->assertRedirect(route('auth.login'));
        $user = User::create(['name' => 'Administrador', 'email' => 'admin@example.test', 'password' => bcrypt('test-password'), 'role' => 'admin', 'is_active' => true]);
        $this->actingAs($user, 'web');
        $page = $this->get($authorizeUrl)->assertOk()->assertSee('Autorizar conexão');
        $approved = $this->post('/oauth/authorize', ['client_id' => $clientId, 'auth_token' => $page->viewData('authToken')])->assertRedirect();
        parse_str(parse_url($approved->headers->get('Location'), PHP_URL_QUERY), $returned);
        $this->assertSame('test-state', $returned['state']);
        $token = $this->postJson('/oauth/token', ['grant_type' => 'authorization_code', 'client_id' => $clientId, 'redirect_uri' => $callback, 'code' => $returned['code'], 'code_verifier' => $verifier, 'resource' => url('/mcp/extractor')])->assertOk();
        $this->assertNotEmpty($token->json('refresh_token'));
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$token->json('access_token'));
        $this->rpc('initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => [], 'clientInfo' => ['name' => 'test', 'version' => '1']])
            ->assertOk()->assertJsonPath('result.serverInfo.name', 'Papirar Extrator');
        $this->callTool('checkPapirarApi')->assertJsonPath('result.isError', false);
    }

    private function signIn(): void
    {
        Passport::actingAs(new User(['role' => 'admin', 'is_active' => true]), ['mcp:use'], 'api');
    }

    private function question(string $statement = 'Qual alternativa apresenta o emprego correto da crase?'): array
    {
        return ['subject_id' => 1, 'topic_id' => 1, 'statement' => $statement, 'question_type' => 'multiple_choice', 'difficulty' => 'easy', 'source_type' => 'authored', 'correct_letter' => 'A', 'alternatives' => [
            ['letter' => 'A', 'text' => 'Fui à escola.'],
            ['letter' => 'B', 'text' => 'Fui à pé.'],
        ]];
    }

    private function rpc(string $method, array $params = [])
    {
        $headers = ['Accept' => 'application/json, text/event-stream', 'MCP-Protocol-Version' => '2025-06-18'];
        if ($method === 'tools/call') {
            $headers['Mcp-Name'] = $params['name'];
        }

        return $this->postJson('/mcp/extractor', ['jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => (object) $params], $headers);
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
