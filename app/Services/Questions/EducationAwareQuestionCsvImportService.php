<?php

namespace App\Services\Questions;

use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportBatchRow;
use App\Support\EducationLevel;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Extensão incremental do importador existente.
 * Preserva os formatos de CSV, a extração por IA, as validações e a deduplicação
 * do serviço original. A escolaridade exige conferência explícita.
 */
class EducationAwareQuestionCsvImportService extends QuestionCsvImportService
{
    public function updatePreviewRow(
        QuestionImportBatch $batch,
        QuestionImportBatchRow $row,
        array $changes
    ): QuestionImportBatchRow {
        $level = $changes['education_level'] ?? null;

        if (! EducationLevel::isValid($level)) {
            throw new InvalidArgumentException('Selecione a escolaridade Médio ou Superior para a questão.');
        }

        // O serviço original valida conteúdo, alternativa, gabarito e duplicidade.
        // O campo adicional é preservado após a normalização da linha.
        $updated = parent::updatePreviewRow($batch, $row, $changes);
        $payload = (array) $updated->raw_data;
        $payload['education_level'] = $level;
        $updated->update(['raw_data' => $payload]);

        return $updated->fresh();
    }

    public function importApprovedRows(
        QuestionImportBatch $batch,
        ?array $rowIds = null,
        int $userId = null
    ): array {
        return DB::transaction(function () use ($batch, $rowIds, $userId): array {
            $query = $batch->rows()->where('status', 'valid');

            if (is_array($rowIds) && $rowIds !== []) {
                $query->whereIn('id', $rowIds);
            }

            $levels = $query->get(['id', 'raw_data'])
                ->mapWithKeys(function (QuestionImportBatchRow $row): array {
                    $level = ((array) $row->raw_data)['education_level'] ?? null;

                    return EducationLevel::isValid($level)
                        ? [$row->id => $level]
                        : [];
                });

            // Os INSERTs e as verificações de duplicidade permanecem no serviço original.
            // A transação externa mantém a gravação do nível atômica com a importação.
            $result = parent::importApprovedRows($batch, $rowIds, $userId);

            if ($levels->isNotEmpty()) {
                $imported = $batch->rows()
                    ->whereIn('id', $levels->keys()->all())
                    ->where('status', 'imported')
                    ->whereNotNull('created_question_id')
                    ->get(['id', 'created_question_id']);

                foreach ($imported as $row) {
                    Question::query()
                        ->whereKey($row->created_question_id)
                        ->whereNull('education_level')
                        ->update(['education_level' => $levels->get($row->id)]);
                }
            }

            return $result;
        });
    }
}
