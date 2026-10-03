<?php

namespace App\Mcp\Tools;

use App\Models\Question;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExtractorApiTool extends Tool
{
    public function __construct(private array $definition)
    {
        $this->name = $definition['name'];
        $this->title = $definition['title'];
        $this->description = $definition['description'];
    }

    public function toArray(): array
    {
        $schema = $this->definition['schema'];
        $schema['properties'] = $schema['properties'] ?: (object) [];
        $security = [['type' => 'oauth2', 'scopes' => ['mcp:use']]];

        return [
            'name' => $this->name(),
            'title' => $this->title(),
            'description' => $this->description(),
            'inputSchema' => $schema,
            'annotations' => [
                'readOnlyHint' => $this->definition['read_only'],
                'destructiveHint' => false,
                'idempotentHint' => $this->definition['read_only'],
                'openWorldHint' => false,
            ],
            'securitySchemes' => $security,
            '_meta' => ['securitySchemes' => $security],
        ];
    }

    public function handle(Request $request): Response
    {
        $user = $request->user();
        if (! $user || ! $user->is_active || ! in_array($user->role, ['admin', 'content'], true) || ! $user->tokenCan('mcp:use')) {
            return Response::error('Acesso MCP não autorizado.');
        }

        $original = app('request');

        try {
            $arguments = $request->all();
            $unknown = array_diff(array_keys($arguments), array_keys($this->definition['schema']['properties']));
            if ($unknown !== []) {
                throw ValidationException::withMessages(['arguments' => ['Campos não permitidos: '.implode(', ', $unknown)]]);
            }
            Validator::make($arguments, $this->rules($this->definition['schema']))->validate();

            $method = $this->definition['method'];
            $apiRequest = HttpRequest::create('/api/gpt'.$this->definition['path'], $method, $method === 'GET' ? $arguments : [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $original->ip(),
                'HTTP_USER_AGENT' => $original->userAgent(),
            ], $method === 'GET' ? null : json_encode($arguments, JSON_THROW_ON_ERROR));
            $apiRequest->setUserResolver(fn () => $user);

            // Paginators resolve their page through the container's current request.
            app()->instance('request', $apiRequest);
            $controller = app('App\\Http\\Controllers\\Api\\Gpt\\'.$this->definition['controller']);
            $parameters = ['request' => $apiRequest];
            if ($this->definition['name'] === 'getQuestion') {
                $parameters['question'] = Question::findOrFail($arguments['question']);
            }

            $response = app()->call([$controller, $this->definition['action']], $parameters);
            $data = $response->getData(true);
            if ($response->getStatusCode() >= 400) {
                return Response::error(json_encode(['http_status' => $response->getStatusCode(), 'response' => $data], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            }

            // Preserve every item of a partial batch; an HTTP 200 is not a promise of full success.
            return Response::json(['http_status' => $response->getStatusCode(), 'response' => $data]);
        } catch (ValidationException $exception) {
            return Response::error(json_encode(['http_status' => 422, 'errors' => $exception->errors()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } catch (ModelNotFoundException $exception) {
            return Response::error('Registro não encontrado (404).');
        } catch (HttpExceptionInterface $exception) {
            return Response::error('Operação recusada ('.$exception->getStatusCode().').');
        } catch (Throwable $exception) {
            report($exception);

            return Response::error('Erro interno. Antes de repetir uma escrita, consulte se o registro já foi criado.');
        } finally {
            app()->instance('request', $original);
        }
    }

    private function rules(array $schema, string $prefix = ''): array
    {
        $rules = [];
        foreach ($schema['properties'] ?? [] as $name => $property) {
            $key = $prefix.$name;
            $required = in_array($name, $schema['required'] ?? [], true);
            $type = $property['type'];
            $field = [$required ? 'required' : 'sometimes'];
            $field[] = match ($type) {
                'object' => 'array:'.implode(',', array_keys($property['properties'] ?? [])),
                'array' => 'array',
                default => $type,
            };
            if (isset($property['enum'])) {
                $field[] = Rule::in($property['enum']);
            }
            foreach (['minimum' => 'min', 'maximum' => 'max', 'minLength' => 'min', 'minItems' => 'min', 'maxItems' => 'max'] as $constraint => $rule) {
                if (isset($property[$constraint])) {
                    $field[] = $rule.':'.$property[$constraint];
                }
            }
            $rules[$key] = $field;
            if ($type === 'object') {
                $rules += $this->rules($property, $key.'.');
            } elseif ($type === 'array') {
                $item = $property['items'];
                if ($item['type'] === 'object') {
                    $rules[$key.'.*'] = ['required', 'array:'.implode(',', array_keys($item['properties']))];
                    $rules += $this->rules($item, $key.'.*.');
                } else {
                    $rules[$key.'.*'] = ['required', $item['type']];
                }
            }
        }

        return $rules;
    }
}
