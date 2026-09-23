<?php

namespace App\Jobs;

use App\Exceptions\GeminiExtractionException;
use App\Models\QuestionImportBatch;
use App\Services\Questions\GeminiQuestionExtractionService;
use App\Services\Questions\QuestionCsvImportService;
use App\Services\Questions\QuestionImportFileCleanupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessAiQuestionImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;
    public int $timeout = 900;

    public function __construct(public int $batchId)
    {
    }

    public function handle(
        GeminiQuestionExtractionService $gemini,
        QuestionCsvImportService $imports,
        QuestionImportFileCleanupService $fileCleanup
    ): void {
        $batch = QuestionImportBatch::query()->findOrFail($this->batchId);

        if ($batch->status === 'cancelled') {
            return;
        }

        $batch->update([
            'status' => 'validating',
            'started_at' => $batch->started_at ?: now(),
            'processing_error' => null,
        ]);

        try {
            $result = $gemini->extract($batch);
            $imports->populatePreviewFromStructuredQuestions($batch, $result['questions']);

            try {
                $fileCleanup->deleteFiles($batch->fresh());
            } catch (\Throwable $cleanupException) {
                Log::warning('A extração foi concluída, mas os arquivos temporários não puderam ser excluídos.', [
                    'batch_id' => $batch->id,
                    'exception' => $cleanupException,
                ]);
            }
        } catch (\Throwable $exception) {
            if ($exception instanceof GeminiExtractionException
                && $exception->temporarilyUnavailable
                && $this->job !== null
                && $this->attempts() < $this->tries) {
                $delays = [60, 180, 420];
                $delay = $delays[$this->attempts() - 1] + random_int(0, 15);

                Log::warning('Gemini indisponível; importação reagendada.', [
                    'batch_id' => $batch->id,
                    'attempt' => $this->attempts(),
                    'retry_in_seconds' => $delay,
                ]);

                $this->release($delay);
                return;
            }

            $batch->update([
                'status' => 'failed',
                'processing_error' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            try {
                DeleteFailedQuestionImportFiles::dispatch($batch->id)
                    ->delay(now()->addHours(24));
            } catch (\Throwable $cleanupDispatchException) {
                Log::warning('Não foi possível agendar a exclusão dos arquivos da importação com falha.', [
                    'batch_id' => $batch->id,
                    'exception' => $cleanupDispatchException,
                ]);
            }

            Log::error('Falha no importador de questões por IA.', [
                'batch_id' => $batch->id,
                'exception' => $exception,
            ]);
        }
    }
}
