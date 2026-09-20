<?php

namespace App\Console\Commands;

use App\Services\Questions\QuestionImportFileCleanupService;
use Illuminate\Console\Command;

class CleanupQuestionImportFiles extends Command
{
    protected $signature = 'questions:cleanup-import-files {--hours=24 : Retenção dos arquivos de lotes com falha}';

    protected $description = 'Exclui arquivos temporários de importações por IA que falharam ou foram canceladas';

    public function handle(QuestionImportFileCleanupService $cleanup): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $result = $cleanup->deleteExpiredFailedFiles($hours);

        $this->info("Limpeza concluída: {$result['batches']} lote(s) e {$result['files']} arquivo(s) removido(s).");

        return self::SUCCESS;
    }
}
