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
        DB::statement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `exam_subject_source_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_subject_id` bigint(20) unsigned NOT NULL,
  `source_material_id` bigint(20) unsigned NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_exam_subject_source_material` (`exam_subject_id`, `source_material_id`),
  KEY `idx_essm_exam_subject_id` (`exam_subject_id`),
  KEY `idx_essm_source_material_id` (`source_material_id`),
  KEY `idx_essm_active` (`active`),
  CONSTRAINT `fk_essm_exam_subject` FOREIGN KEY (`exam_subject_id`) REFERENCES `exam_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_essm_source_material` FOREIGN KEY (`source_material_id`) REFERENCES `source_materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        // Documental. Não usar rollback em produção.
    }
};
