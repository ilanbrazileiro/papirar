<?php

namespace App\Services\Questions;

use App\Exceptions\GeminiExtractionException;
use App\Models\QuestionImportAiAttempt;
use App\Models\QuestionImportBatch;
use App\Models\Subject;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;
use ZipArchive;

class GeminiQuestionExtractionService
{
    public function extract(QuestionImportBatch $batch): array
    {
        $apiKey = (string) config('services.gemini.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY não configurada.');
        }

        $primary = (string) config('services.gemini.primary_model', 'gemini-3.8-flash');
        $fallback = (string) config('services.gemini.fallback_model', 'gemini-3.5-flash-lite');

        try {
            return $this->request($batch, $primary, $apiKey);
        } catch (GeminiExtractionException $exception) {
            if ($exception->temporarilyUnavailable) {
                sleep(max(1, (int) config('services.gemini.retry_delay', 10)));

                try {
                    return $this->request($batch, $primary, $apiKey);
                } catch (GeminiExtractionException $retryException) {
                    $exception = $retryException;
                }
            }

            $canFallback = $exception->quotaExceeded || $exception->temporarilyUnavailable;

            if (!$canFallback || $fallback === '' || $fallback === $primary) {
                throw $exception;
            }

            return $this->request($batch, $fallback, $apiKey);
        }
    }

    private function request(QuestionImportBatch $batch, string $model, string $apiKey): array
    {
        $attempt = QuestionImportAiAttempt::query()->create([
            'batch_id' => $batch->id,
            'model' => $model,
            'status' => 'started',
            'started_at' => now(),
        ]);

        $batch->increment('ai_attempts');
        $batch->update(['ai_model' => $model]);

        try {
            $response = Http::acceptJson()
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout((int) config('services.gemini.timeout', 600))
                ->post($this->endpoint($model), $this->payload($batch));

            if (!$response->successful()) {
                throw $this->apiException($response);
            }

            $body = $response->json();
            $text = data_get($body, 'candidates.0.content.parts.0.text');

            if (!is_string($text) || trim($text) === '') {
                throw new GeminiExtractionException('O Gemini não devolveu conteúdo estruturado.');
            }

            try {
                $result = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new GeminiExtractionException('O Gemini devolveu JSON inválido: '.$exception->getMessage());
            }

            if (!isset($result['questions']) || !is_array($result['questions'])) {
                throw new GeminiExtractionException('O JSON devolvido não contém a lista questions.');
            }

            $attempt->update([
                'status' => 'success',
                'usage' => $body['usageMetadata'] ?? null,
                'finished_at' => now(),
            ]);

            $batch->update(['ai_model' => $model]);

            return $result;
        } catch (GeminiExtractionException $exception) {
            $attempt->update([
                'status' => 'failed',
                'http_status' => $exception->httpStatus,
                'error_code' => $exception->errorCode,
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        } catch (\Throwable $exception) {
            $attempt->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }

    private function endpoint(string $model): string
    {
        return rtrim((string) config('services.gemini.base_url'), '/')
            .'/models/'.rawurlencode($model).':generateContent';
    }

    private function payload(QuestionImportBatch $batch): array
    {
        $parts = [
            ...$this->fileParts($batch),
            ['text' => $this->prompt($batch)],
        ];

        return [
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0,
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => $this->schema(),
            ],
        ];
    }

    private function prompt(QuestionImportBatch $batch): string
    {
        $batch->loadMissing(['corporation', 'exam', 'examBoard']);

        $taxonomy = Subject::query()
            ->where('active', true)
            ->with(['topics' => fn ($query) => $query->where('active', true)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $subject) => [
                'subject_id' => $subject->id,
                'name' => $subject->name,
                'topics' => $subject->topics->map(fn ($topic) => [
                    'topic_id' => $topic->id,
                    'name' => $topic->name,
                ])->values()->all(),
            ])->values()->all();

        $context = [
            'corporation' => $batch->corporation?->name,
            'exam' => $batch->exam?->title,
            'year' => $batch->exam?->year,
            'informed_year' => $batch->exam_year,
            'position_or_exam' => $batch->exam_reference,
            'exam_board' => $batch->examBoard?->name,
            'source_type' => $batch->source_type,
        ];

        return <<<'PROMPT'
Você é um extrator documental. Transcreva fielmente todas as questões objetivas e associe o gabarito informado no documento ou no arquivo separado. Não resuma, não reescreva, não corrija e não crie comentários. Não invente questões nem respostas. Preserve parágrafos e marcações relevantes em HTML simples. A saída desta versão sempre contém as letras A-E. Se alguma alternativa não existir no original, use texto vazio nessa letra e registre o problema em warnings; nunca invente conteúdo. Informe has_image=true quando a questão depender de imagem, gráfico, tabela ou diagrama. Classifique usando exclusivamente os IDs fornecidos na taxonomia. Nunca crie disciplina ou tópico. Se não houver tópico seguro, use topic_id null. A confiança deve variar de 0 a 1. Informe a página do documento quando identificável. Questões anuladas devem usar null em correct_letter e cancelled=true.

CONTEXTO DA ORIGEM:
PROMPT
            .json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ."\n\nTAXONOMIA PERMITIDA:\n"
            .json_encode($taxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function fileParts(QuestionImportBatch $batch): array
    {
        $parts = [];

        foreach ([
            ['path' => $batch->source_file_path, 'label' => 'ARQUIVO DA PROVA'],
            ['path' => $batch->answer_file_path, 'label' => 'ARQUIVO DO GABARITO'],
        ] as $file) {
            if (!$file['path']) {
                continue;
            }

            $absolutePath = Storage::disk('local')->path($file['path']);

            if (!is_file($absolutePath)) {
                throw new RuntimeException("{$file['label']} não encontrado no armazenamento.");
            }

            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $parts[] = ['text' => $file['label']];

            if ($extension === 'docx') {
                $parts[] = ['text' => $this->extractDocxText($absolutePath)];
                continue;
            }

            $mime = match ($extension) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                default => throw new RuntimeException("Formato .{$extension} não suportado."),
            };

            $parts[] = ['inlineData' => [
                'mimeType' => $mime,
                'data' => base64_encode((string) file_get_contents($absolutePath)),
            ]];
        }

        return $parts;
    }

    private function extractDocxText(string $path): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão PHP Zip é necessária para processar DOCX.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Não foi possível abrir o arquivo DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!is_string($xml)) {
            throw new RuntimeException('O DOCX não contém word/document.xml.');
        }

        $xml = str_replace(['</w:p>', '</w:tr>', '<w:tab/>'], ["\n", "\n", "\t"], $xml);
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", (string) $text);

        return trim((string) $text);
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['questions'],
            'properties' => [
                'questions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['original_number', 'statement', 'alternatives', 'correct_letter', 'subject_id', 'confidence', 'cancelled', 'has_image'],
                        'properties' => [
                            'original_number' => ['type' => 'integer'],
                            'page_number' => ['type' => ['integer', 'null']],
                            'statement' => ['type' => 'string'],
                            'alternatives' => [
                                'type' => 'array',
                                'minItems' => 5,
                                'maxItems' => 5,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['letter', 'text'],
                                    'properties' => [
                                        'letter' => ['type' => 'string', 'enum' => ['A', 'B', 'C', 'D', 'E']],
                                        'text' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                            'correct_letter' => ['type' => ['string', 'null'], 'enum' => ['A', 'B', 'C', 'D', 'E', null]],
                            'subject_id' => ['type' => 'integer'],
                            'topic_id' => ['type' => ['integer', 'null']],
                            'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                            'cancelled' => ['type' => 'boolean'],
                            'has_image' => ['type' => 'boolean'],
                            'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function apiException(Response $response): GeminiExtractionException
    {
        $status = $response->status();
        $code = data_get($response->json(), 'error.status');
        $message = data_get($response->json(), 'error.message') ?: 'Falha na API Gemini.';
        $quotaExceeded = $status === 429 || $code === 'RESOURCE_EXHAUSTED';
        $temporarilyUnavailable = $status === 503 || $code === 'UNAVAILABLE';

        return new GeminiExtractionException(
            $message,
            $status,
            $code,
            $quotaExceeded,
            $temporarilyUnavailable
        );
    }
}
