<?php

return [
    'database' => [
        'enabled' => (bool) env('DB_BACKUP_ENABLED', false),
        'path' => env('DB_BACKUP_PATH'),
        'retention_days' => (int) env('DB_BACKUP_RETENTION_DAYS', 14),
        'filename_prefix' => env('DB_BACKUP_FILENAME_PREFIX', 'papirar-db'),
        'dump_binary' => env('DB_DUMP_BINARY', 'mysqldump'),
        'timeout_seconds' => (int) env('DB_BACKUP_TIMEOUT_SECONDS', 1800),
    ],
];
