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

        $primary = (string) config('services.gemini.primary_model', 'gemini-3.5-flash');
        $fallback = (string) config('services.gemini.fallback_model', 'gemini-3.5-flash-lite');

        $index = $this->requestWithFallback($batch, $primary, $fallback, $apiKey, true);
        $numbers = $this->expectedNumbers($index);
        $questions = [];

        foreach (array_chunk($numbers, 12) as $chunk) {
            $this->collectQuestions($batch, $primary, $fallback, $apiKey, $chunk, $questions);
        }

        ksort($questions, SORT_NUMERIC);

        return ['questions' => array_values($questions)];
    }

    private function requestWithFallback(
        QuestionImportBatch $batch,
        string $primary,
        string $fallback,
        string $apiKey,
        bool $indexOnly = false,
        array $numbers = []
    ): array {
        try {
            return $this->request($batch, $primary, $apiKey, $indexOnly, $numbers);
        } catch (GeminiExtractionException $exception) {
            if ($exception->temporarilyUnavailable) {
                sleep(max(1, (int) config('services.gemini.retry_delay', 10)));

                try {
                    return $this->request($batch, $primary, $apiKey, $indexOnly, $numbers);
                } catch (GeminiExtractionException $retryException) {
                    $exception = $retryException;
                }
            }

            $canFallback = $exception->quotaExceeded || $exception->temporarilyUnavailable;

            if (!$canFallback || $fallback === '' || $fallback === $primary) {
                throw $exception;
            }

            return $this->request($batch, $fallback, $apiKey, $indexOnly, $numbers);
        }
    }

    private function expectedNumbers(array $index): array
    {
        $first = $index['first_number'] ?? null;
        $last = $index['last_number'] ?? null;
        $found = $index['question_numbers'] ?? null;

        if (!is_int($first) || !is_int($last) || $first < 1 || $last < $first
            || $last - $first >= 300 || !is_array($found) || $found === []) {
            throw new GeminiExtractionException('Não foi possível identificar a numeração completa da prova. Confira o arquivo e tente novamente.');
        }

        $numbers = range($first, $last);
        $reported = array_values(array_unique(array_filter($found, fn ($n) => is_int($n) && $n >= $first && $n <= $last)));
        if (!in_array($first, $reported, true) || !in_array($last, $reported, true)) {
            throw new GeminiExtractionException('A conferência inicial não identificou os extremos da prova. Nenhuma questão foi importada.');
        }

        return $numbers;
    }

    private function collectQuestions(
        QuestionImportBatch $batch,
        string $primary,
        string $fallback,
        string $apiKey,
        array $numbers,
        array &$questions
    ): void {
        $missing = $numbers;

        for ($try = 0; $try < 2 && $missing !== []; $try++) {
            try {
                $result = $this->requestWithFallback($batch, $primary, $fallback, $apiKey, false, $missing);
            } catch (GeminiExtractionException $exception) {
                if ($exception->errorCode === 'INCOMPLETE_RESPONSE' && $try === 0) {
                    continue;
                }
                throw $exception;
            }

            foreach ($result['questions'] as $question) {
                $number = $question['original_number'] ?? null;
                if (is_int($number) && in_array($number, $numbers, true)
                    && !empty(trim((string) ($question['statement'] ?? '')))) {
                    $questions[$number] = $question;
                }
            }

            $missing = array_values(array_diff($numbers, array_keys($questions)));
        }

        if ($missing !== []) {
            throw new GeminiExtractionException('Extração incompleta: faltaram as questões '.implode(', ', $missing).'. Nenhuma questão deste lote foi importada.');
        }
    }

    private function request(QuestionImportBatch $batch, string $model, string $apiKey, bool $indexOnly, array $numbers): array
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
                ->post($this->endpoint($model), $this->payload($batch, $indexOnly, $numbers));

            if (!$response->successful()) {
                throw $this->apiException($response);
            }

            $body = $response->json();
            $finishReason = data_get($body, 'candidates.0.finishReason');
            if ($finishReason !== null && $finishReason !== 'STOP') {
                throw new GeminiExtractionException(
                    'A resposta da IA foi interrompida ('.$finishReason.'). Tente novamente com o documento.',
                    null,
                    $finishReason === 'MAX_TOKENS' ? 'INCOMPLETE_RESPONSE' : (string) $finishReason
                );
            }
            $text = data_get($body, 'candidates.0.content.parts.0.text');

            if (!is_string($text) || trim($text) === '') {
                throw new GeminiExtractionException('O Gemini não devolveu conteúdo estruturado.');
            }

            try {
                $result = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new GeminiExtractionException('O Gemini devolveu JSON inválido: '.$exception->getMessage());
            }

            if ($indexOnly) {
                if (!is_array($result) || !isset($result['question_numbers']) || !is_array($result['question_numbers'])) {
                    throw new GeminiExtractionException('A IA não devolveu o índice das questões.');
                }
            } elseif (!isset($result['questions']) || !is_array($result['questions'])) {
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

    private function payload(QuestionImportBatch $batch, bool $indexOnly, array $numbers): array
    {
        $parts = [
            ...$this->fileParts($batch, !$indexOnly),
            ['text' => $indexOnly ? $this->indexPrompt() : $this->prompt($batch, $numbers)],
        ];

        return [
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0,
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => $indexOnly ? $this->indexSchema() : $this->schema(),
            ],
        ];
    }

    private function indexPrompt(): string
    {
        return 'Leia TODAS as páginas do ARQUIVO DA PROVA, inclusive a primeira e a última página com questões. '
            .'Identifique somente a numeração impressa das questões objetivas da prova; ignore números de páginas, exemplos, sumário e gabarito. '
            .'Informe first_number (primeira questão da prova), last_number (última questão da prova) '
            .'e question_numbers (todos os números de questões encontrados, em ordem). '
            .'Verifique especialmente o fim do documento. Se houver numeração contínua, percorra até a última questão; não interrompa a leitura na metade.';
    }

    private function indexSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['first_number', 'last_number', 'question_numbers'],
            'properties' => [
                'first_number' => ['type' => 'integer'],
                'last_number' => ['type' => 'integer'],
                'question_numbers' => ['type' => 'array', 'items' => ['type' => 'integer']],
            ],
        ];
    }

    private function prompt(QuestionImportBatch $batch, array $numbers): string
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

        $instruction = 'Extraia SOMENTE as questões de números '.implode(', ', $numbers).'. '
            .'Consulte o documento inteiro para localizar cada número, seu texto-base e o gabarito. '
            .'Retorne uma entrada por número solicitado, na ordem. Não pule números. '
            .'Nunca invente uma questão que não está no documento.' . "\n\n";

        return $instruction . <<<'PROMPT'
Você é um extrator documental. Transcreva fielmente todas as questões objetivas e associe o gabarito informado no documento ou no arquivo separado. Não resuma, não reescreva, não corrija e não crie comentários. Não invente questões nem respostas. A saída sempre contém as letras A-E. Se uma alternativa não existir, use texto vazio e registre o problema em warnings; nunca invente conteúdo.

Para interpretação, transcreva integralmente o texto-base, poema, tabela textual ou trecho compartilhado em passage, mesmo quando ele aparecer em página anterior. Repita esse passage para cada questão que o utiliza. Em statement coloque apenas o comando e o enunciado específico da questão; não repita ali o passage. Se não houver texto-base, use passage vazio. Se o texto-base for necessário e estiver ilegível ou ausente, marque missing_passage=true e avise em warnings; não o invente. Nos demais casos use missing_passage=false. Preserve a ordem, os parágrafos e as marcações originais em HTML simples com <p>. Deixe cada parágrafo justificado com style="text-align: justify;". Não crie negritos ou ênfases ausentes do documento. Faça o mesmo com os parágrafos das alternativas.

Informe has_image=true quando a questão depender de imagem, gráfico, tabela visual ou diagrama. Não crie imagens artificiais. Classifique somente com IDs existentes na taxonomia. Nunca crie disciplina ou tópico. Se nenhuma disciplina encaixar com segurança, use subject_id null, topic_id null e preencha suggested_subject_name, suggested_topic_name e classification_reason com sua sugestão e motivo breve. Se houver disciplina existente mas não tópico seguro, mantenha subject_id e use topic_id null; pode sugerir o tópico. A confiança deve variar de 0 a 1. Informe a página quando identificável. Questões anuladas usam correct_letter null e cancelled=true.

CONTEXTO DA ORIGEM:
PROMPT
            .json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ."\n\nTAXONOMIA PERMITIDA:\n"
            .json_encode($taxonomy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function fileParts(QuestionImportBatch $batch, bool $includeAnswer): array
    {
        $parts = [];

        foreach ([
            ['path' => $batch->source_file_path, 'label' => 'ARQUIVO DA PROVA'],
            ['path' => $batch->answer_file_path, 'label' => 'ARQUIVO DO GABARITO'],
        ] as $file) {
            if (!$file['path']) {
                continue;
            }
            if (!$includeAnswer && $file['label'] === 'ARQUIVO DO GABARITO') {
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
                        'required' => ['original_number', 'passage', 'missing_passage', 'statement', 'alternatives', 'correct_letter', 'subject_id', 'confidence', 'cancelled', 'has_image'],
                        'properties' => [
                            'original_number' => ['type' => 'integer'],
                            'page_number' => ['type' => ['integer', 'null']],
                            'passage' => ['type' => 'string'],
                            'missing_passage' => ['type' => 'boolean'],
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
                            'subject_id' => ['type' => ['integer', 'null']],
                            'topic_id' => ['type' => ['integer', 'null']],
                            'suggested_subject_name' => ['type' => ['string', 'null']],
                            'suggested_topic_name' => ['type' => ['string', 'null']],
                            'classification_reason' => ['type' => ['string', 'null']],
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
