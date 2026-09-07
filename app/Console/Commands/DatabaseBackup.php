<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database {--no-cleanup : Não remover backups antigos nesta execução}';
    protected $description = 'Cria um backup compactado do banco MySQL/MariaDB e aplica a retenção configurada';

    public function handle(): int
    {
        if (! config('backup.database.enabled', false)) {
            $this->components->info('Backup automático desativado.');
            return self::SUCCESS;
        }

        $connectionName = (string) config('database.default');
        $database = (array) config("database.connections.{$connectionName}", []);

        if (($database['driver'] ?? null) !== 'mysql') {
            $this->components->error('O comando de backup suporta apenas MySQL/MariaDB.');
            return self::FAILURE;
        }

        $directory = $this->backupDirectory();

        if (! function_exists('gzopen')) {
            $this->components->error('A extensão zlib do PHP é necessária para compactar o backup.');
            return self::FAILURE;
        }

        File::ensureDirectoryExists($directory, 0700, true);

        $prefix = preg_replace('/[^a-z0-9_-]+/i', '-', (string) config('backup.database.filename_prefix', 'papirar-db'));
        $filename = trim((string) $prefix, '-') . '-' . now()->format('Y-m-d_His') . '.sql.gz';
        $finalPath = $directory . DIRECTORY_SEPARATOR . $filename;
        $temporaryPath = $finalPath . '.part';
        $gzip = null;

        try {
            $gzip = gzopen($temporaryPath, 'wb9');
            if ($gzip === false) {
                throw new RuntimeException('Não foi possível criar o arquivo temporário do backup.');
            }

            $errorOutput = '';
            $process = new Process($this->dumpCommand($database), null, [
                'MYSQL_PWD' => (string) ($database['password'] ?? ''),
            ]);
            $process->setTimeout((int) config('backup.database.timeout_seconds', 1800));
            $process->run(function (string $type, string $buffer) use ($gzip, &$errorOutput): void {
                if ($type === Process::OUT) {
                    if (gzwrite($gzip, $buffer) === false) {
                        throw new RuntimeException('Falha ao gravar o arquivo compactado.');
                    }
                    return;
                }
                $errorOutput .= $buffer;
            });

            gzclose($gzip);
            $gzip = null;

            if (! $process->isSuccessful()) {
                throw new RuntimeException(trim($errorOutput) ?: 'O mysqldump terminou com erro.');
            }
            if (! File::exists($temporaryPath) || File::size($temporaryPath) < 100) {
                throw new RuntimeException('O arquivo gerado está vazio ou incompleto.');
            }

            File::move($temporaryPath, $finalPath);
            if (! $this->option('no-cleanup')) {
                $this->removeExpiredBackups($directory);
            }

            Log::info('Backup diário do banco concluído.', ['file' => $filename, 'bytes' => File::size($finalPath)]);
            $this->components->info("Backup criado: {$finalPath}");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            if (is_resource($gzip)) {
                gzclose($gzip);
            }
            File::delete($temporaryPath);
            Log::error('Falha no backup diário do banco.', ['message' => $exception->getMessage()]);
            $this->components->error('Backup não concluído: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }

    private function dumpCommand(array $database): array
    {
        return [
            (string) config('backup.database.dump_binary', 'mysqldump'),
            '--single-transaction', '--quick', '--routines', '--triggers', '--events', '--hex-blob', '--no-tablespaces',
            '--default-character-set=utf8mb4',
            '--host=' . (string) ($database['host'] ?? '127.0.0.1'),
            '--port=' . (string) ($database['port'] ?? '3306'),
            '--user=' . (string) ($database['username'] ?? ''),
            (string) ($database['database'] ?? ''),
        ];
    }

    private function backupDirectory(): string
    {
        $configured = trim((string) config('backup.database.path'));
        return $configured !== '' ? rtrim($configured, '\\/') : storage_path('app/private/backups/database');
    }

    private function removeExpiredBackups(string $directory): void
    {
        $retentionDays = max(2, (int) config('backup.database.retention_days', 14));
        $cutoff = Carbon::now()->subDays($retentionDays)->getTimestamp();

        foreach (File::glob($directory . DIRECTORY_SEPARATOR . '*.sql.gz') ?: [] as $file) {
            if (File::lastModified($file) < $cutoff) {
                File::delete($file);
            }
        }
    }
}
