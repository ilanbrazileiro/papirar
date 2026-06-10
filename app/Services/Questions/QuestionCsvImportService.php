<?php

namespace App\Services\Questions;

use App\Models\Corporation;
use App\Models\Exam;
use App\Models\Question;
use App\Models\SourceMaterial;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuestionCsvImportService
{
    public function import(string $path, bool $dryRun = false, ?int $userId = null): array
    {
        $handle = fopen($path, 'r');

        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Não foi possível abrir o arquivo enviado.',
                'inserted' => 0,
                'validated_rows' => 0,
                'errors' => [],
            ];
        }

        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            fclose($handle);

            return [
                'success' => false,
                'message' => 'Arquivo vazio ou cabeçalho inválido.',
                'inserted' => 0,
                'validated_rows' => 0,
                'errors' => [],
            ];
        }

        $normalizedHeader = array_map(fn ($item) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $item)), $header);
        $headerType = $this->detectHeaderType($normalizedHeader);

        if (!$headerType) {
            fclose($handle);

            return [
                'success' => false,
                'message' => 'Cabeçalho do CSV inválido. Baixe e use o modelo oficial.',
                'inserted' => 0,
                'validated_rows' => 0,
                'errors' => [],
                'expected_header' => $this->expectedHeaderWithSourceMaterial(),
                'legacy_header' => $this->legacyExpectedHeader(),
                'received_header' => $normalizedHeader,
            ];
        }

        $line = 1;
        $inserted = 0;
        $validatedRows = 0;
        $errors = [];
        $rows = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $line++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $expectedHeader = $headerType === 'new'
                ? $this->expectedHeaderWithSourceMaterial()
                : $this->legacyExpectedHeader();

            $payload = array_combine($expectedHeader, array_pad($row, count($expectedHeader), null));

            if ($headerType === 'legacy') {
                $payload['source_material_id'] = null;
            }

            try {
                $rows[] = $this->validateRow($payload, $line);
                $validatedRows++;
            } catch (RuntimeException $e) {
                $errors[] = [
                    'line' => $line,
                    'message' => $e->getMessage(),
                ];
            }
        }

        fclose($handle);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Importação interrompida. Corrija os erros e tente novamente.',
                'inserted' => 0,
                'validated_rows' => $validatedRows,
                'errors' => $errors,
            ];
        }

        if ($dryRun) {
            return [
                'success' => true,
                'message' => 'Validação concluída com sucesso. Nenhuma questão foi inserida porque o modo simulação estava ativo.',
                'inserted' => 0,
                'validated_rows' => $validatedRows,
                'errors' => [],
            ];
        }

        DB::transaction(function () use ($rows, $userId, &$inserted) {
            foreach ($rows as $data) {
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
                    'status' => $data['status'],
                    'created_by' => $userId,
                ]);

                foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
                    $question->alternatives()->create([
                        'letter' => $letter,
                        'text' => $data['alternatives'][$letter],
                        'is_correct' => $data['correct_letter'] === $letter,
                    ]);
                }

                $inserted++;
            }
        });

        return [
            'success' => true,
            'message' => "{$inserted} questão(ões) importada(s) com sucesso.",
            'inserted' => $inserted,
            'validated_rows' => $validatedRows,
            'errors' => [],
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
        $corporationId = isset($row['corporation_id']) && trim((string) $row['corporation_id']) !== ''
            ? (int) $row['corporation_id']
            : null;
        $examId = isset($row['exam_id']) && trim((string) $row['exam_id']) !== ''
            ? (int) $row['exam_id']
            : null;
        $subjectId = $this->nullableInt($row['subject_id']);
        $topicId = $this->nullableInt($row['topic_id']);
        $sourceMaterialId = $this->nullableInt($row['source_material_id'] ?? null);
        $statement = trim((string) $row['statement']);
        $questionType = trim((string) $row['question_type']);
        $difficulty = trim((string) $row['difficulty']);
        $sourceType = trim((string) $row['source_type']);
        $sourceReference = $this->nullableString($row['source_reference']);
        $commentedAnswer = $this->nullableString($row['commented_answer']);
        $status = trim((string) $row['status']);
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

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            throw new RuntimeException("Linha {$line}: status inválido.");
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
            'status' => $status,
            'correct_letter' => $correctLetter,
            'alternatives' => $alternatives,
        ];
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
