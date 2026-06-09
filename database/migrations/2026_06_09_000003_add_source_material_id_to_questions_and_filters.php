<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MIGRATION DOCUMENTAL - NÃO EXECUTAR EM PRODUÇÃO SEM REVISÃO.
     * A atualização real deve ser feita pelo SQL do lote.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `questions` ADD COLUMN `source_material_id` bigint(20) unsigned DEFAULT NULL AFTER `source_reference`");
        DB::statement("ALTER TABLE `saved_filters` ADD COLUMN `source_material_id` bigint(20) unsigned DEFAULT NULL AFTER `source_type`");
        DB::statement("ALTER TABLE `study_sessions` ADD COLUMN `source_material_id` bigint(20) unsigned DEFAULT NULL AFTER `topic_id`");
        DB::statement("ALTER TABLE `simulated_exams` ADD COLUMN `source_material_id` bigint(20) unsigned DEFAULT NULL AFTER `topic_id`");
    }

    public function down(): void
    {
        // Documental. Não usar rollback em produção.
    }
};
