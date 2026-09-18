<?php

namespace App\Jobs;

use App\Models\QuestionImportBatch;
use App\Services\Questions\GeminiQuestionExtractionService;
use App\Services\Questions\QuestionCsvImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessAiQuestionImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 900;

    public function __construct(public int $batchId)
    {
    }

    public function handle(
        GeminiQuestionExtractionService $gemini,
        QuestionCsvImportService $imports
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
        } catch (\Throwable $exception) {
            $batch->update([
                'status' => 'failed',
                'processing_error' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('Falha no importador de questões por IA.', [
                'batch_id' => $batch->id,
                'exception' => $exception,
            ]);
        }
    }
}
