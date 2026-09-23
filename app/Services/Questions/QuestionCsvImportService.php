<?php

namespace App\Services\Questions;

use App\Models\Corporation;
use App\Models\Exam;
use App\Models\ExamBoard;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportBatchRow;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class QuestionCsvImportService
{
    private ?array $aiDuplicateIndex = null;

    public function populatePreviewFromStructuredQuestions(QuestionImportBatch $batch, array $questions): QuestionImportBatch
    {
        $batch->loadMissing(['corporation', 'exam', 'examBoard']);
        $totalRows = 0;
        $validRows = 0;
        $duplicateRows = 0;
        $errorRows = 0;
        $seenInBatch = [];
        $confidenceThreshold = (float) config('services.gemini.classification_confidence', 0.75);

        DB::transaction(function () use (
            $batch,
            $questions,
            &$totalRows,
            &$validRows,
            &$duplicateRows,
            &$errorRows,
            &$seenInBatch,
            $confidenceThreshold
        ) {
            $batch->rows()->delete();

            foreach (array_values($questions) as $index => $question) {
                $rowNumber = (int) ($question['original_number'] ?? ($index + 1));
                $totalRows++;
                $alternatives = collect($question['alternatives'] ?? [])->mapWithKeys(
                    fn ($alternative) => [strtoupper((string) ($alternative['letter'] ?? '')) => trim((string) ($alternative['text'] ?? ''))]
                );
                $warnings = array_values(array_filter((array) ($question['warnings'] ?? [])));
                $confidence = max(0, min(1, (float) ($question['confidence'] ?? 0)));
                $questionText = $this->formatExtractedHtml((string) ($question['statement'] ?? ''));
                $passage = $this->formatExtractedHtml((string) ($question['passage'] ?? ''));
                $payload = [
                    'corporation_id' => $batch->corporation_id,
                    'exam_id' => $batch->exam_id,
                    'subject_id' => $question['subject_id'] ?? null,
                    'topic_id' => $question['topic_id'] ?? null,
                    'exam_board_id' => $batch->exam_board_id,
                    'exam_board' => null,
                    'statement' => trim($passage."\n".$questionText),
                    'question_type' => 'multiple_choice',
                    'difficulty' => 'medium',
                    'source_type' => $batch->source_type ?: 'exam',
                    'source_reference' => $this->buildAutomaticReference($batch),
                    'source_material_id' => $batch->source_material_id,
                    'commented_answer' => null,
                    'status' => 'draft',
                    'alternative_a' => $this->formatExtractedHtml((string) $alternatives->get('A')),
                    'alternative_b' => $this->formatExtractedHtml((string) $alternatives->get('B')),
                    'alternative_c' => $this->formatExtractedHtml((string) $alternatives->get('C')),
                    'alternative_d' => $this->formatExtractedHtml((string) $alternatives->get('D')),
                    'alternative_e' => $this->formatExtractedHtml((string) $alternatives->get('E')),
                    'correct_letter' => $question['correct_letter'] ?? null,
                    '_meta' => [
                        'original_number' => $rowNumber,
                        'page_number' => $question['page_number'] ?? null,
                        'classification_confidence' => $confidence,
                        'has_image' => (bool) ($question['has_image'] ?? false),
                        'needs_human_review' => $confidence < $confidenceThreshold || !empty($question['has_image']) || !empty($question['missing_passage']),
                        'missing_passage' => (bool) ($question['missing_passage'] ?? false),
                        'warnings' => $warnings,
                        'question_text' => $questionText,
                        'passage' => $passage,
                        'suggested_subject_name' => trim((string) ($question['suggested_subject_name'] ?? '')),
                        'suggested_topic_name' => trim((string) ($question['suggested_topic_name'] ?? '')),
                        'classification_reason' => trim((string) ($question['classification_reason'] ?? '')),
                    ],
                ];

                if (!empty($question['cancelled'])) {
                    $errorRows++;
                    QuestionImportBatchRow::query()->create([
                        'batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'status' => 'error',
                        'raw_data' => $payload,
                        'error_message' => 'Questão anulada no gabarito oficial. Confirme antes de cadastrar.',
                    ]);
                    continue;
                }

                try {
                    $validated = $this->validateRow($payload, $rowNumber);
                    $validated['_meta'] = $payload['_meta'];
                    $normalizedStatement = $this->normalizeText($validated['statement']);
                    [$duplicateQuestionId, $similar] = $this->findAiDuplicate($validated['statement'], $questionText);
                    $batchKey = $normalizedStatement;
                    $duplicateInBatch = isset($seenInBatch[$batchKey]);

                    if ($duplicateQuestionId || $duplicateInBatch) {
                        $duplicateRows++;
                        QuestionImportBatchRow::query()->create([
                            'batch_id' => $batch->id,
                            'row_number' => $rowNumber,
                            'status' => 'duplicate',
                            'raw_data' => $validated,
                            'normalized_statement' => $normalizedStatement,
                            'error_message' => $duplicateInBatch
                                ? 'Possível duplicidade dentro do próprio lote.'
                                : ($similar ? 'Possível questão semelhante já cadastrada. Confira antes de importar.' : 'Questão com enunciado idêntico já encontrada no banco.'),
                            'duplicate_question_id' => $duplicateQuestionId,
                        ]);
                        continue;
                    }

                    $seenInBatch[$batchKey] = true;
                    $needsReview = $confidence < $confidenceThreshold || !empty($question['has_image']) || !empty($question['missing_passage']);
                    $needsReview ? $errorRows++ : $validRows++;
                    QuestionImportBatchRow::query()->create([
                        'batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'status' => $needsReview ? 'error' : 'valid',
                        'raw_data' => $validated,
                        'normalized_statement' => $normalizedStatement,
                        'error_message' => $needsReview
                            ? (!empty($question['has_image'])
                                ? 'A questão depende de elemento visual. Confira o original e inclua a imagem antes de importar.'
                                : (!empty($question['missing_passage'])
                                    ? 'Texto-base ausente ou ilegível. Confira e inclua o texto antes de importar.'
                                    : 'Classificação com baixa confiança. Confira disciplina e tópico antes de importar.'))
                            : ($warnings ? implode(' | ', $warnings) : null),
                    ]);
                } catch (RuntimeException $exception) {
                    $errorRows++;
                    QuestionImportBatchRow::query()->create([
                        'batch_id' => $batch->id,
                        'row_number' => $rowNumber,
                        'status' => 'error',
                        'raw_data' => $payload,
                        'error_message' => $exception->getMessage(),
                    ]);
                }
            }

            $batch->update([
                'status' => $totalRows > 0 ? 'ready' : 'failed',
                'total_rows' => $totalRows,
                'valid_rows' => $validRows,
                'duplicate_rows' => $duplicateRows,
                'error_rows' => $errorRows,
                'processing_error' => null,
                'finished_at' => now(),
            ]);
        });

        return $batch->fresh(['rows']);
    }

    public function updatePreviewRow(QuestionImportBatch $batch, QuestionImportBatchRow $row, array $changes): QuestionImportBatchRow
    {
        if ((int) $row->batch_id !== (int) $batch->id) {
            throw new RuntimeException('A linha não pertence ao lote informado.');
        }

        if ($row->status === 'imported') {
            throw new RuntimeException('Uma linha já importada não pode ser alterada.');
        }

        $payload = array_merge((array) $row->raw_data, $changes);
        $payload['alternative_a'] = $changes['alternatives']['A'] ?? $payload['alternative_a'] ?? data_get($payload, 'alternatives.A');
        $payload['alternative_b'] = $changes['alternatives']['B'] ?? $payload['alternative_b'] ?? data_get($payload, 'alternatives.B');
        $payload['alternative_c'] = $changes['alternatives']['C'] ?? $payload['alternative_c'] ?? data_get($payload, 'alternatives.C');
        $payload['alternative_d'] = $changes['alternatives']['D'] ?? $payload['alternative_d'] ?? data_get($payload, 'alternatives.D');
        $payload['alternative_e'] = $changes['alternatives']['E'] ?? $payload['alternative_e'] ?? data_get($payload, 'alternatives.E');
        $meta = (array) ($payload['_meta'] ?? []);
        $meta['needs_human_review'] = false;
        $meta['manually_checked'] = true;
        $meta['duplicate_override'] = !empty($changes['allow_duplicate']);
        if ($batch->import_type === 'ai') {
            $editedStatement = trim((string) ($changes['statement'] ?? ''));
            $passage = (string) ($meta['passage'] ?? '');
            $meta['question_text'] = $passage !== '' && str_starts_with($editedStatement, $passage)
                ? trim(substr($editedStatement, strlen($passage)))
                : $editedStatement;
        }

        try {
            $validated = $this->validateRow($payload, $row->row_number);
            $validated['_meta'] = $meta;
            $normalized = $this->normalizeText($validated['statement']);
            [$duplicateId, $similar] = $batch->import_type === 'ai'
                ? $this->findAiDuplicate($validated['statement'], (string) ($meta['question_text'] ?? ''))
                : [$this->findExactDuplicateQuestionId($normalized, $validated['subject_id'], $validated['topic_id']), false];

            $row->update([
                'status' => $duplicateId && !$meta['duplicate_override'] ? 'duplicate' : 'valid',
                'raw_data' => $validated,
                'normalized_statement' => $normalized,
                'duplicate_question_id' => $duplicateId && !$meta['duplicate_override'] ? $duplicateId : null,
                'error_message' => $duplicateId && !$meta['duplicate_override']
                    ? ($similar ? 'Possível questão semelhante já cadastrada. Confira antes de importar.' : 'Questão com enunciado idêntico já encontrada no banco.')
                    : null,
            ]);
        } catch (RuntimeException $exception) {
            $row->update([
                'status' => 'error',
                'raw_data' => $payload,
                'error_message' => $exception->getMessage(),
                'duplicate_question_id' => null,
            ]);
        }

        $this->refreshBatchCounters($batch);

        return $row->fresh();
    }

    public function createPreview(UploadedFile $file, int $userId): QuestionImportBatch
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            throw new RuntimeException('Não foi possível abrir o arquivo enviado.');
        }

        return $this->createPreviewFromHandle($handle, $userId, $file->getClientOriginalName());
    }

    public function createPreviewFromText(string $content, int $userId, string $filename = 'csv_colado.csv'): QuestionImportBatch
    {
        $content = trim((string) $content);

        if ($content === '') {
            throw new RuntimeException('Cole o conteúdo do CSV antes de analisar.');
        }

        $handle = fopen('php://temp', 'r+');

        if (!$handle) {
            throw new RuntimeException('Não foi possível preparar o conteúdo colado para análise.');
        }

        fwrite($handle, $content);
        rewind($handle);

        return $this->createPreviewFromHandle($handle, $userId, $filename);
    }

    private function createPreviewFromHandle($handle, int $userId, string $filename): QuestionImportBatch
    {
        $batch = QuestionImportBatch::query()->create([
            'user_id' => $userId,
            'filename' => $filename,
            'original_filename' => $filename,
            'status' => 'validating',
            'total_rows' => 0,
            'valid_rows' => 0,
            'imported_rows' => 0,
            'draft_rows' => 0,
            'duplicate_rows' => 0,
            'error_rows' => 0,
            'ignored_rows' => 0,
            'started_at' => now(),
        ]);

        $firstLine = fgets($handle);

        if ($firstLine === false) {
            fclose($handle);
            $this->failHeader($batch, ['message' => 'CSV vazio ou cabeçalho inválido.'], 'CSV vazio ou cabeçalho inválido.');
            return $batch;
        }

        $delimiter = $this->detectDelimiter($firstLine);
        $header = str_getcsv($firstLine, $delimiter);
        $normalizedHeader = $this->normalizeHeader($header);
        $headerSpec = $this->detectHeaderSpec($normalizedHeader);

        if (!$headerSpec) {
            fclose($handle);
            $this->failHeader($batch, [
                'received_header' => $normalizedHeader,
                'accepted_headers' => $this->acceptedHeaders(),
            ], $this->invalidHeaderMessage($normalizedHeader));
            return $batch;
        }

        $expectedHeader = $headerSpec['header'];
        $headerType = $headerSpec['type'];
        $line = 1;
        $totalRows = 0;
        $validRows = 0;
        $duplicateRows = 0;
        $errorRows = 0;
        $seenInFile = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $totalRows++;
            $payload = array_combine($expectedHeader, array_pad($row, count($expectedHeader), null));

            if ($headerType === 'legacy') {
                $payload['source_material_id'] = null;
                $payload['exam_board_id'] = null;
                $payload['exam_board'] = null;
            }

            if ($headerType === 'source_material') {
                $payload['exam_board_id'] = null;
                $payload['exam_board'] = null;
            }

            if ($headerType === 'exam_board_id') {
                $payload['exam_board'] = null;
            }

            try {
                $validated = $this->validateRow($payload, $line);
                $normalizedStatement = $this->normalizeText($validated['statement']);
                $duplicateQuestionId = $this->findExactDuplicateQuestionId(
                    $normalizedStatement,
                    $validated['subject_id'],
                    $validated['topic_id']
                );

                $fileDuplicateKey = $validated['subject_id'].'|'.($validated['topic_id'] ?? 'null').'|'.$normalizedStatement;
                $duplicateInFile = isset($seenInFile[$fileDuplicateKey]);

                if ($duplicateQuestionId || $duplicateInFile) {
                    $duplicateRows++;
                    QuestionImportBatchRow::query()->create([
                        'batch_id' => $batch->id,
                        'row_number' => $line,
                        'status' => 'duplicate',
                        'raw_data' => $payload,
                        'normalized_statement' => $normalizedStatement,
                        'error_message' => $duplicateInFile
                            ? 'Possível duplicidade dentro do próprio CSV colado/arquivo.'
                            : 'Questão com enunciado idêntico já encontrada no banco.',
                        'duplicate_question_id' => $duplicateQuestionId ?: null,
                    ]);

                    continue;
                }

                $seenInFile[$fileDuplicateKey] = true;
                $validRows++;

                QuestionImportBatchRow::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $line,
                    'status' => 'valid',
                    'raw_data' => $validated,
                    'normalized_statement' => $normalizedStatement,
                ]);
            } catch (RuntimeException $e) {
                $errorRows++;

                QuestionImportBatchRow::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $line,
                    'status' => 'error',
                    'raw_data' => $payload,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        fclose($handle);

        $batch->update([
            'status' => $validRows > 0 ? 'ready' : 'failed',
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'duplicate_rows' => $duplicateRows,
            'error_rows' => $errorRows,
            'finished_at' => now(),
        ]);

        return $batch->fresh(['rows']);
    }

    public function importApprovedRows(QuestionImportBatch $batch, ?array $rowIds = null, int $userId = null): array
    {
        if (!in_array($batch->status, ['ready', 'partial'], true)) {
            throw new RuntimeException('Este lote não está disponível para importação.');
        }

        $query = $batch->rows()->where('status', 'valid');

        if (is_array($rowIds) && count($rowIds) > 0) {
            $query->whereIn('id', $rowIds);
        }

        $rows = $query->orderBy('row_number')->get();

        if ($rows->isEmpty()) {
            throw new RuntimeException('Nenhuma linha válida foi selecionada para importação.');
        }

        $inserted = 0;
        $duplicatesFound = 0;

        DB::transaction(function () use ($rows, $batch, $userId, &$inserted, &$duplicatesFound) {
            foreach ($rows as $batchRow) {
                $data = $batchRow->raw_data;

                if ($batch->import_type === 'ai' && empty($data['_meta']['duplicate_override'])) {
                    [$duplicateId, $similar] = $this->findAiDuplicate(
                        (string) $data['statement'],
                        (string) ($data['_meta']['question_text'] ?? '')
                    );
                    if ($duplicateId) {
                        $batchRow->update([
                            'status' => 'duplicate',
                            'duplicate_question_id' => $duplicateId,
                            'error_message' => $similar
                                ? 'Possível questão semelhante já cadastrada. Confira antes de importar.'
                                : 'Questão com enunciado idêntico já encontrada no banco.',
                        ]);
                        $duplicatesFound++;
                        continue;
                    }
                }

                $questionData = [
                    'corporation_id' => $data['corporation_id'] ?? null,
                    'exam_id' => $data['exam_id'] ?? null,
                    'subject_id' => $data['subject_id'],
                    'topic_id' => $data['topic_id'] ?? null,
                    'exam_board_id' => $data['exam_board_id'] ?? null,
                    'statement' => $data['statement'],
                    'question_type' => $data['question_type'],
                    'difficulty' => $data['difficulty'],
                    'source_type' => $data['source_type'],
                    'source_reference' => $data['source_reference'] ?? null,
                    'source_material_id' => $data['source_material_id'] ?? null,
                    'commented_answer' => $data['commented_answer'] ?? null,
                    'status' => 'draft',
                    'created_by' => $userId,
                ];

                if (Schema::hasColumn('questions', 'question_import_batch_id')) {
                    $questionData['question_import_batch_id'] = $batchRow->batch_id;
                }

                $question = Question::query()->create($questionData);
                if ($batch->import_type === 'ai' && $this->aiDuplicateIndex !== null) {
                    $this->indexAiQuestion($question);
                }

                foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
                    $question->alternatives()->create([
                        'letter' => $letter,
                        'text' => $data['alternatives'][$letter],
                        'is_correct' => $data['correct_letter'] === $letter,
                    ]);
                }

                $batchRow->update([
                    'status' => 'imported',
                    'created_question_id' => $question->id,
                    'raw_data' => null,
                    'error_message' => null,
                ]);

                $inserted++;
            }
        });

        $this->refreshBatchCounters($batch);

        return [
            'inserted' => $inserted,
            'message' => "{$inserted} questão(ões) importada(s) como rascunho."
                .($duplicatesFound ? " {$duplicatesFound} duplicata(s) encontrada(s) na conferência final." : ''),
        ];
    }

    public function refreshBatchCounters(QuestionImportBatch $batch): void
    {
        $rows = $batch->rows()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $imported = (int) ($rows['imported'] ?? 0);
        $valid = (int) ($rows['valid'] ?? 0);
        $duplicates = (int) ($rows['duplicate'] ?? 0);
        $errors = (int) ($rows['error'] ?? 0);
        $ignored = (int) ($rows['ignored'] ?? 0);

        $openRows = $valid + $duplicates + $errors;

        if ($valid > 0) {
            $status = $imported > 0 ? 'partial' : 'ready';
        } elseif ($imported > 0 && $openRows === 0) {
            $status = 'imported';
        } elseif ($imported > 0) {
            $status = 'partial';
        } elseif ($ignored > 0 && $openRows === 0) {
            $status = 'cancelled';
        } else {
            $status = $errors > 0 || $duplicates > 0 ? 'failed' : 'failed';
        }

        $batch->update([
            'valid_rows' => $valid,
            'imported_rows' => $imported,
            'draft_rows' => $imported,
            'duplicate_rows' => $duplicates,
            'error_rows' => $errors,
            'ignored_rows' => $ignored,
            'status' => $status,
            'finished_at' => $valid === 0 && $imported > 0 ? now() : $batch->finished_at,
        ]);
    }

    /** Compatibilidade com o fluxo antigo, caso algum ponto do sistema ainda chame import(). */
    public function import(string $path, bool $dryRun = false, ?int $userId = null): array
    {
        return [
            'success' => false,
            'message' => 'O importador agora usa pré-validação. Envie o CSV pela tela de importação para revisar o lote antes de gravar.',
            'inserted' => 0,
            'validated_rows' => 0,
            'errors' => [],
        ];
    }

    private function detectDelimiter(string $line): string
    {
        $candidates = [';' => substr_count($line, ';'), ',' => substr_count($line, ','), "\t" => substr_count($line, "\t")];
        arsort($candidates);
        $delimiter = array_key_first($candidates);

        return $candidates[$delimiter] > 0 ? $delimiter : ';';
    }

    private function normalizeHeader(array $header): array
    {
        return array_map(fn ($item) => $this->canonicalHeaderName($item), $header);
    }

    private function canonicalHeaderName(mixed $value): string
    {
        $value = (string) $value;
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = str_replace(["\u{FEFF}", "\u{00A0}"], '', $value);
        $value = trim($value);
        $value = trim($value, "\"'` ");
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(['-', ' '], '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return match ($value) {
            'banca', 'exam_board', 'exam_board_name', 'nome_banca', 'banca_nome' => 'exam_board',
            'banca_id', 'exam_board_id', 'id_banca' => 'exam_board_id',
            'bibliografia_id', 'fonte_id', 'material_id', 'source_material_id' => 'source_material_id',
            default => $value,
        };
    }

    private function detectHeaderSpec(array $header): ?array
    {
        foreach ($this->headerSpecs() as $spec) {
            if ($header === $spec['header']) {
                return $spec;
            }
        }

        return null;
    }

    private function headerSpecs(): array
    {
        return [
            ['type' => 'exam_board_name', 'header' => $this->expectedHeaderWithExamBoardName()],
            ['type' => 'exam_board_id', 'header' => $this->expectedHeaderWithExamBoard()],
            ['type' => 'source_material', 'header' => $this->expectedHeaderWithSourceMaterial()],
            ['type' => 'legacy', 'header' => $this->legacyExpectedHeader()],
        ];
    }

    private function acceptedHeaders(): array
    {
        return collect($this->headerSpecs())
            ->mapWithKeys(fn ($spec) => [$spec['type'] => implode(';', $spec['header'])])
            ->all();
    }

    private function invalidHeaderMessage(array $receivedHeader): string
    {
        return 'Cabeçalho do CSV inválido. Cabeçalho recebido: '
            .implode(';', $receivedHeader)
            .' | Modelo atual: '
            .implode(';', $this->expectedHeaderWithExamBoardName());
    }

    private function failHeader(QuestionImportBatch $batch, array $rawData, string $message): void
    {
        $batch->update([
            'status' => 'failed',
            'error_rows' => 1,
            'finished_at' => now(),
        ]);

        QuestionImportBatchRow::query()->create([
            'batch_id' => $batch->id,
            'row_number' => 1,
            'status' => 'error',
            'raw_data' => $rawData,
            'error_message' => $message,
        ]);
    }

    private function expectedHeaderWithExamBoardName(): array
    {
        return [
            'corporation_id',
            'exam_id',
            'subject_id',
            'topic_id',
            'exam_board_id',
            'exam_board',
            'statement',
            'question_type',
            'difficulty',
            'source_type',
            'source_reference',
            'source_material_id',
            'commented_answer',
            'status',
            'alternative_a',
            'alternative_b',
            'alternative_c',
            'alternative_d',
            'alternative_e',
            'correct_letter',
        ];
    }

    private function expectedHeaderWithSourceMaterial(): array
    {
        return [
            'corporation_id',
            'exam_id',
            'subject_id',
            'topic_id',
            'statement',
            'question_type',
            'difficulty',
            'source_type',
            'source_reference',
            'source_material_id',
            'commented_answer',
            'status',
            'alternative_a',
            'alternative_b',
            'alternative_c',
            'alternative_d',
            'alternative_e',
            'correct_letter',
        ];
    }

    private function expectedHeaderWithExamBoard(): array
    {
        return [
            'corporation_id',
            'exam_id',
            'subject_id',
            'topic_id',
            'exam_board_id',
            'statement',
            'question_type',
            'difficulty',
            'source_type',
            'source_reference',
            'source_material_id',
            'commented_answer',
            'status',
            'alternative_a',
            'alternative_b',
            'alternative_c',
            'alternative_d',
            'alternative_e',
            'correct_letter',
        ];
    }

    private function legacyExpectedHeader(): array
    {
        return [
            'corporation_id',
            'exam_id',
            'subject_id',
            'topic_id',
            'statement',
            'question_type',
            'difficulty',
            'source_type',
            'source_reference',
            'commented_answer',
            'status',
            'alternative_a',
            'alternative_b',
            'alternative_c',
            'alternative_d',
            'alternative_e',
            'correct_letter',
        ];
    }

    private function validateRow(array $row, int $line): array
    {
        $corporationId = isset($row['corporation_id']) && trim((string) $row['corporation_id']) !== '' ? (int) $row['corporation_id'] : null;
        $examId = isset($row['exam_id']) && trim((string) $row['exam_id']) !== '' ? (int) $row['exam_id'] : null;
        $subjectId = $this->nullableInt($row['subject_id']);
        $topicId = $this->nullableInt($row['topic_id']);
        $examBoardId = $this->resolveExamBoardId($row['exam_board_id'] ?? null, $row['exam_board'] ?? null, $line);
        $sourceMaterialId = $this->nullableInt($row['source_material_id'] ?? null);
        $statement = trim((string) $row['statement']);
        $questionType = trim((string) $row['question_type']);
        $difficulty = trim((string) $row['difficulty']);
        $sourceType = trim((string) $row['source_type']);
        $sourceReference = $this->nullableString($row['source_reference']);
        $commentedAnswer = $this->nullableString($row['commented_answer']);
        $correctLetter = strtoupper(trim((string) $row['correct_letter']));

        if ($corporationId && !Corporation::query()->whereKey($corporationId)->exists()) {
            throw new RuntimeException("Linha {$line}: corporation_id inválido ou inexistente.");
        }

        if (!$subjectId || !Subject::query()->whereKey($subjectId)->exists()) {
            throw new RuntimeException("Linha {$line}: subject_id inválido ou inexistente.");
        }

        if ($examId && !Exam::query()->whereKey($examId)->exists()) {
            throw new RuntimeException("Linha {$line}: exam_id inválido ou inexistente.");
        }

        if ($topicId && !Topic::query()->whereKey($topicId)->exists()) {
            throw new RuntimeException("Linha {$line}: topic_id inválido ou inexistente.");
        }

        if ($topicId) {
            $topic = Topic::query()->find($topicId);
            if ($topic && (int) $topic->subject_id !== (int) $subjectId) {
                throw new RuntimeException("Linha {$line}: topic_id não pertence ao subject_id informado.");
            }
        }

        if ($sourceMaterialId) {
            $material = SourceMaterial::query()->find($sourceMaterialId);
            if (!$material) {
                throw new RuntimeException("Linha {$line}: source_material_id inválido ou inexistente.");
            }
            if ((int) $material->subject_id !== (int) $subjectId) {
                throw new RuntimeException("Linha {$line}: source_material_id não pertence ao subject_id informado.");
            }
            if ($corporationId && $material->corporation_id && (int) $material->corporation_id !== (int) $corporationId) {
                throw new RuntimeException("Linha {$line}: source_material_id pertence a outra corporação.");
            }
        }

        if ($statement === '') {
            throw new RuntimeException("Linha {$line}: enunciado obrigatório.");
        }

        if ($questionType !== 'multiple_choice') {
            throw new RuntimeException("Linha {$line}: question_type deve ser multiple_choice.");
        }

        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw new RuntimeException("Linha {$line}: difficulty inválida.");
        }

        if (!in_array($sourceType, ['exam', 'authored', 'adapted'], true)) {
            throw new RuntimeException("Linha {$line}: source_type inválido.");
        }

        if (!in_array($correctLetter, ['A', 'B', 'C', 'D', 'E'], true)) {
            throw new RuntimeException("Linha {$line}: correct_letter inválida.");
        }

        $alternatives = [
            'A' => trim((string) $row['alternative_a']),
            'B' => trim((string) $row['alternative_b']),
            'C' => trim((string) $row['alternative_c']),
            'D' => trim((string) $row['alternative_d']),
            'E' => trim((string) $row['alternative_e']),
        ];

        foreach ($alternatives as $letter => $text) {
            if ($text === '') {
                throw new RuntimeException("Linha {$line}: alternativa {$letter} obrigatória.");
            }
        }

        return [
            'corporation_id' => $corporationId,
            'exam_id' => $examId,
            'subject_id' => $subjectId,
            'topic_id' => $topicId,
            'exam_board_id' => $examBoardId,
            'source_material_id' => $sourceMaterialId,
            'statement' => $statement,
            'question_type' => $questionType,
            'difficulty' => $difficulty,
            'source_type' => $sourceType,
            'source_reference' => $sourceReference,
            'commented_answer' => $commentedAnswer,
            'status' => 'draft',
            'correct_letter' => $correctLetter,
            'alternatives' => $alternatives,
        ];
    }

    private function resolveExamBoardId(mixed $idValue, mixed $nameValue, int $line): ?int
    {
        $idValue = trim((string) ($idValue ?? ''));
        $nameValue = trim((string) ($nameValue ?? ''));

        if ($idValue !== '') {
            if (ctype_digit($idValue)) {
                $id = (int) $idValue;
                if (!ExamBoard::query()->whereKey($id)->exists()) {
                    throw new RuntimeException("Linha {$line}: exam_board_id inválido ou inexistente.");
                }
                return $id;
            }

            return $this->findExamBoardByName($idValue, $line);
        }

        if ($nameValue !== '') {
            return $this->findExamBoardByName($nameValue, $line);
        }

        return null;
    }

    private function findExamBoardByName(string $name, int $line): int
    {
        $normalizedName = trim($name);
        $slug = Str::slug($normalizedName);

        $examBoard = ExamBoard::query()
            ->where('name', $normalizedName)
            ->orWhere('slug', $slug)
            ->first();

        if (!$examBoard) {
            throw new RuntimeException("Linha {$line}: banca '{$normalizedName}' não encontrada. Cadastre a banca no Admin ou informe exam_board_id válido.");
        }

        return (int) $examBoard->id;
    }

    /** @return array{0: ?int, 1: bool} */
    private function findAiDuplicate(string $statement, string $questionText = ''): array
    {
        if ($this->aiDuplicateIndex === null) {
            $this->aiDuplicateIndex = ['exact' => [], 'prefix' => []];
            Question::query()->select(['id', 'statement'])->orderBy('id')->chunkById(500, function ($questions): void {
                foreach ($questions as $question) {
                    $this->indexAiQuestion($question);
                }
            });
        }

        $full = $this->normalizeText($statement);
        $core = $this->normalizeText($questionText);
        foreach (array_unique([$full, $core]) as $text) {
            $minimumLength = $text === $core && $core !== $full ? 100 : 40;
            if (mb_strlen($text) >= $minimumLength && isset($this->aiDuplicateIndex['exact'][$text])) {
                return [$this->aiDuplicateIndex['exact'][$text], false];
            }
        }

        // Somente candidatos com começo igual e comprimento próximo: OCR e pequenas variações.
        // Textos-base compartilhados podem ocupar quase todo o enunciado;
        // a similaridade deve comparar o comando da questão, não o texto-base.
        foreach (($core !== '' ? [$core] : [$full]) as $text) {
            if (mb_strlen($text) < 100) {
                continue;
            }
            $prefix = mb_substr($text, 0, 24);
            foreach ($this->aiDuplicateIndex['prefix'][$prefix] ?? [] as [$candidate, $id]) {
                if (abs(mb_strlen($candidate) - mb_strlen($text)) > mb_strlen($text) * 0.06) {
                    continue;
                }
                similar_text($text, $candidate, $percent);
                if ($percent >= 96) {
                    return [$id, true];
                }
            }
        }

        return [null, false];
    }

    private function indexAiQuestion(Question $question): void
    {
        $normalized = $this->normalizeText($question->statement);
        if (mb_strlen($normalized) < 40) {
            return;
        }
        $this->aiDuplicateIndex['exact'][$normalized] ??= (int) $question->id;
        $prefix = mb_substr($normalized, 0, 24);
        $this->aiDuplicateIndex['prefix'][$prefix][] = [$normalized, (int) $question->id];
    }

    private function formatExtractedHtml(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        if (preg_match('/<p\b/i', $text)) {
            return preg_replace_callback('/<p\b([^>]*)>/i', function (array $match): string {
                $attributes = $match[1];
                if (preg_match('/\bstyle\s*=\s*(["\']).*?\1/is', $attributes)) {
                    $attributes = preg_replace_callback('/\bstyle\s*=\s*(["\'])(.*?)\1/is', function (array $style): string {
                        $css = preg_replace('/(?:^|;)\s*text-align\s*:[^;]*/i', '', $style[2]);
                        return 'style="'.trim((string) $css, ' ;').'; text-align: justify;"';
                    }, $attributes);
                    return '<p'.$attributes.'>';
                }
                return '<p'.$attributes.' style="text-align: justify;">';
            }, $text) ?? $text;
        }

        if (preg_match('/<[^>]+>/', $text)) {
            if (preg_match('/<(?:table|div|ul|ol|blockquote|h[1-6])\b/i', $text)) {
                return '<div style="text-align: justify;">'.$text.'</div>';
            }
            return '<p style="text-align: justify;">'.$text.'</p>';
        }

        return collect(preg_split('/\R{2,}/u', $text))
            ->map(fn ($paragraph) => '<p style="text-align: justify;">'.nl2br(e(trim($paragraph))).'</p>')
            ->implode("\n");
    }

    private function findExactDuplicateQuestionId(string $normalizedStatement, int $subjectId, ?int $topicId = null): ?int
    {
        if ($normalizedStatement === '') {
            return null;
        }

        $query = Question::query()
            ->select(['id', 'statement'])
            ->where('subject_id', $subjectId);

        if ($topicId) {
            $query->where('topic_id', $topicId);
        }

        foreach ($query->limit(1000)->get() as $question) {
            if ($this->normalizeText($question->statement) === $normalizedStatement) {
                return (int) $question->id;
            }
        }

        return null;
    }

    private function normalizeText(?string $text): string
    {
        $text = preg_replace('/<\/(?:p|div|li|tr|h[1-6])\s*>/i', ' ', (string) $text);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::ascii(mb_strtolower($text, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        return trim((string) $text);
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function buildAutomaticReference(QuestionImportBatch $batch): string
    {
        if ($batch->source_type === 'authored') {
            return 'Papirar Concursos - Questão autoral';
        }

        $parts = array_filter([
            $batch->examBoard?->name,
            $batch->exam_year,
            $batch->exam_reference,
        ], fn ($value) => $value !== null && $value !== '');

        if ($batch->source_type === 'adapted') {
            $parts[] = 'Questão adaptada';
        }

        return mb_substr($parts ? implode(' - ', $parts) : 'Origem não informada', 0, 255);
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
