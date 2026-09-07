<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `study_sessions` MODIFY COLUMN `mode` ENUM('train','exam','review','favorites') NOT NULL DEFAULT 'train'");
    }

    public function down(): void
    {
        DB::table('study_sessions')
            ->whereIn('mode', ['review', 'favorites'])
            ->update(['mode' => 'train']);

        DB::statement("ALTER TABLE `study_sessions` MODIFY COLUMN `mode` ENUM('train','exam') NOT NULL DEFAULT 'train'");
    }
};
