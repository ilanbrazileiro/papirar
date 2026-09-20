<?php

namespace App\Jobs;

use App\Models\QuestionImportBatch;
use App\Services\Questions\QuestionImportFileCleanupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteFailedQuestionImportFiles implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public int $batchId)
    {
    }

    public function handle(QuestionImportFileCleanupService $cleanup): void
    {
        $batch = QuestionImportBatch::query()->find($this->batchId);

        if (!$batch || !in_array($batch->status, ['failed', 'cancelled'], true)) {
            return;
        }

        if ($batch->finished_at && $batch->finished_at->isAfter(now()->subHours(24))) {
            $delay = max(60, (int) now()->diffInSeconds($batch->finished_at->copy()->addHours(24), false));
            $this->release($delay);

            return;
        }

        $cleanup->deleteFiles($batch);
    }
}
