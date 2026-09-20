<?php

namespace App\Services\Questions;

use App\Models\QuestionImportBatch;
use Illuminate\Support\Facades\Storage;

class QuestionImportFileCleanupService
{
    public function deleteFiles(QuestionImportBatch $batch): int
    {
        $paths = array_values(array_unique(array_filter([
            $batch->source_file_path,
            $batch->answer_file_path,
        ])));

        if ($paths === []) {
            return 0;
        }

        $disk = Storage::disk('local');
        $deleted = 0;

        foreach ($paths as $path) {
            if ($disk->exists($path) && $disk->delete($path)) {
                $deleted++;
            }
        }

        $directory = dirname($paths[0]);

        if ($directory !== '.' && $directory !== '') {
            $disk->deleteDirectory($directory);
        }

        $batch->update([
            'source_file_path' => null,
            'answer_file_path' => null,
        ]);

        return $deleted;
    }

    public function deleteExpiredFailedFiles(int $hours = 24): array
    {
        $cutoff = now()->subHours(max(1, $hours));
        $batches = 0;
        $files = 0;

        QuestionImportBatch::query()
            ->where('import_type', 'ai')
            ->whereIn('status', ['failed', 'cancelled'])
            ->where(function ($query) {
                $query->whereNotNull('source_file_path')
                    ->orWhereNotNull('answer_file_path');
            })
            ->where(function ($query) use ($cutoff) {
                $query->where('finished_at', '<=', $cutoff)
                    ->orWhere(function ($nested) use ($cutoff) {
                        $nested->whereNull('finished_at')
                            ->where('updated_at', '<=', $cutoff);
                    });
            })
            ->orderBy('id')
            ->chunkById(100, function ($expiredBatches) use (&$batches, &$files) {
                foreach ($expiredBatches as $batch) {
                    $files += $this->deleteFiles($batch);
                    $batches++;
                }
            });

        return compact('batches', 'files');
    }
}
