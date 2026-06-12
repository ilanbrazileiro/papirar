<?php

namespace App\Services\Questions;

use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportBatchRow;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuestionCsvImportService
{
    public function import(string $path, bool $dryRun = false, ?int $userId = null, ?string $originalFilename = null): array
    {
        $batch = QuestionImportBatch::query()->create([
            'user_id' => $userId,
            'filename' => basename($path),
            'original_filename' => $originalFilename,
            'status' => 'validating',
            'started_at' => now(),
        ]);

        $handle = fopen($path, 'r');

        if (!$handle) {
            $batch->update([
                'status' => 'failed',
                'finished_at' => now(),
                'notes' => 'Não foi possível abrir o arquivo enviado.',
            ]);

            return [
                'success' => false,
                'message' => 'Não foi possível abrir o arquivo enviado.',
                'batch_id' => $batch->id,
                'inserted' => 0,
                'validated_rows' => 0,
                'errors' => [],
            ];
        }

        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            fclose($handle);

            $batch->update([
                'status' => 'failed',
                'finished_at' => now(),
                'notes' => 'Arquivo vazio ou cabeçalho inválido.',
            ]);

            return [
                'success' => false,
                'message' => 'Arquivo vazio ou cabeçalho inválido.',
                'batch_id' => $batch->id,
                'inserted' => 0,
                'validated_rows' => 0,
                'errors' => [],
            ];
        }

        $normalizedHeader = array_map(fn ($item) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $item)), $header);
        $headerType = $this->detectHeaderType($normalizedHeader);

        if (!$headerType) {
            fclose($handle);

            $batch->update([
                'status' => 'failed',
                'finished_at' => now(),
                'notes' => 'Cabeçalho do CSV inválido.',
            ]);

            return [
                'success' => false,
                'message' => 'Cabeçalho do CSV inválido. Baixe e use o modelo oficial.',
                'batch_id' => $batch->id,
                'inserted' => 0,
                'validated_rows' => 0,
                'errors' => [],
                'expected_header' => $this->expectedHeaderWithSourceMaterial(),
                'legacy_header' => $this->legacyExpectedHeader(),
                'received_header' => $normalizedHeader,
            ];
        }

        $line = 1;
        $totalRows = 0;
        $validatedRows = 0;
        $duplicateRows = 0;
        $errorRows = 0;
        $errors = [];
        $duplicates = [];
        $rows = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $line++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $totalRows++;
            $expectedHeader = $headerType === 'new' ? $this->expectedHeaderWithSourceMaterial() : $this->legacyExpectedHeader();
            $payload = array_combine($expectedHeader, array_pad($row, count($expectedHeader), null));

            if ($headerType === 'legacy') {
                $payload['source_material_id'] = null;
            }

            try {
                $validated = $this->validateRow($payload, $line);
                $normalizedStatement = $this->normalizeStatement($validated['statement']);
                $duplicateQuestion = $this->findExactDuplicate($normalizedStatement, $validated['subject_id'], $validated['topic_id']);

                if ($duplicateQuestion) {
                    $duplicateRows++;
                    $duplicates[] = [
                        'line' => $line,
                        'message' => "Linha {$line}: possível duplicidade exata com a questão #{$duplicateQuestion->id}.",
                        'question_id' => $duplicateQuestion->id,
                    ];

                    QuestionImportBatchRow::query()->create([
                        'batch_id' => $batch->id,
                        'row_number' => $line,
                        'status' => 'duplicate',
                        'raw_data' => $payload,
                        'normalized_statement' => $normalizedStatement,
                        'error_message' => "Possível duplicidade exata com a questão #{$duplicateQuestion->id}.",
                        'duplicate_question_id' => $duplicateQuestion->id,
                    ]);

                    continue;
                }

                $validated['normalized_statement'] = $normalizedStatement;
                $rows[] = [
                    'line' => $line,
                    'payload' => $payload,
                    'data' => $validated,
                ];
                $validatedRows++;
            } catch (RuntimeException $e) {
                $errorRows++;
                $errors[] = [
                    'line' => $line,
                    'message' => $e->getMessage(),
                ];

                QuestionImportBatchRow::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $line,
                    'status' => 'error',
                    'raw_data' => $payload,
                    'normalized_statement' => isset($payload['statement']) ? $this->normalizeStatement((string) $payload['statement']) : null,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        fclose($handle);

        $batch->update([
            'total_rows' => $totalRows,
            'valid_rows' => $validatedRows,
            'duplicate_rows' => $duplicateRows,
            'error_rows' => $errorRows,
        ]);

        if (!empty($errors) || !empty($duplicates)) {
            $batch->update([
                'status' => 'failed',
                'finished_at' => now(),
                'notes' => 'Importação interrompida por erros ou duplicidades. Nenhuma questão foi inserida.',
            ]);

            return [
                'success' => false,
                'message' => 'Importação interrompida. Corrija os erros/duplicidades e tente novamente.',
                'batch_id' => $batch->id,
                'inserted' => 0,
                'validated_rows' => $validatedRows,
                'errors' => $errors,
                'duplicates' => $duplicates,
            ];
        }

        if ($dryRun) {
            foreach ($rows as $row) {
                QuestionImportBatchRow::query()->updateOrCreate(
                    [
                        'batch_id' => $batch->id,
                        'row_number' => $row['line'],
                    ],
                    [
                        'status' => 'valid',
                        'raw_data' => null,
                        'normalized_statement' => $row['data']['normalized_statement'],
                    ]
                );
            }

            $batch->update([
                'status' => 'ready',
                'finished_at' => now(),
                'notes' => 'Validação concluída no modo simulação. Nenhuma questão foi inserida.',
            ]);

            return [
                'success' => true,
                'message' => 'Validação concluída com sucesso. Nenhuma questão foi inserida porque o modo simulação estava ativo.',
                'batch_id' => $batch->id,
                'inserted' => 0,
                'validated_rows' => $validatedRows,
                'errors' => [],
                'duplicates' => [],
            ];
        }

        $inserted = 0;

        DB::transaction(function () use ($rows, $userId, $batch, &$inserted) {
            $batch->update(['status' => 'importing']);

            foreach ($rows as $row) {
                $data = $row['data'];

                $question = Question::query()->create([
                    'corporation_id' => $data['corporation_id'],
                    'exam_id' => $data['exam_id'],
                    'subject_id' => $data['subject_id'],
                    'topic_id' => $data['topic_id'],
                    'statement' => $data['statement'],
                    'question_type' => $data['question_type'],
                    'difficulty' => $data['difficulty'],
                    'source_type' => $data['source_type'],
                    'source_reference' => $data['source_reference'],
                    'source_material_id' => $data['source_material_id'],
                    'commented_answer' => $data['commented_answer'],
                    'status' => 'draft',
                    'created_by' => $userId,
                ]);

                foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
                    $question->alternatives()->create([
                        'letter' => $letter,
                        'text' => $data['alternatives'][$letter],
                        'is_correct' => $data['correct_letter'] === $letter,
                    ]);
                }

                QuestionImportBatchRow::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $row['line'],
                    'status' => 'imported',
                    'raw_data' => null,
                    'normalized_statement' => $data['normalized_statement'],
                    'created_question_id' => $question->id,
                ]);

                $inserted++;
            }
        });

        $batch->update([
            'status' => 'imported',
            'imported_rows' => $inserted,
            'draft_rows' => $inserted,
            'finished_at' => now(),
            'notes' => 'Questões importadas como rascunho.',
        ]);

        return [
            'success' => true,
            'message' => "{$inserted} questão(ões) importada(s) como rascunho.",
            'batch_id' => $batch->id,
            'inserted' => $inserted,
            'validated_rows' => $validatedRows,
            'errors' => [],
            'duplicates' => [],
        ];
    }

    private function detectHeaderType(array $header): ?string
    {
        if ($header === $this->expectedHeaderWithSourceMaterial()) {
            return 'new';
        }

        if ($header === $this->legacyExpectedHeader()) {
            return 'legacy';
        }

        return null;
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

    private function findExactDuplicate(string $normalizedStatement, int $subjectId, ?int $topicId): ?Question
    {
        $questions = Question::query()
            ->where('subject_id', $subjectId)
            ->when($topicId, fn ($query) => $query->where('topic_id', $topicId))
            ->select(['id', 'statement', 'subject_id', 'topic_id'])
            ->get();

        foreach ($questions as $question) {
            if ($this->normalizeStatement((string) $question->statement) === $normalizedStatement) {
                return $question;
            }
        }

        return null;
    }

    private function normalizeStatement(string $statement): string
    {
        $text = html_entity_decode(strip_tags($statement), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return mb_strtolower(trim($text));
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = trim((string) $value);
        return $value === '' ? null : (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
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
