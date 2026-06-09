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
CREATE TABLE IF NOT EXISTS `source_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corporation_id` bigint(20) unsigned DEFAULT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `material_type` enum('manual','lei','edital','norma','portaria','apostila','outro') NOT NULL DEFAULT 'manual',
  `year` int(11) DEFAULT NULL,
  `reference_code` varchar(100) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_materials_slug` (`slug`),
  KEY `idx_source_materials_corporation_id` (`corporation_id`),
  KEY `idx_source_materials_subject_id` (`subject_id`),
  KEY `idx_source_materials_active` (`active`),
  CONSTRAINT `fk_source_materials_corporation` FOREIGN KEY (`corporation_id`) REFERENCES `corporations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_source_materials_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        // Documental. Não usar rollback em produção.
    }
};
