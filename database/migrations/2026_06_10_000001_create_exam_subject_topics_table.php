<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration documental para consulta futura.
     * O usuário optou por não rodar migrations em produção.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `exam_subject_topics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `exam_subject_id` bigint(20) unsigned NOT NULL,
  `topic_id` bigint(20) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_exam_subject_topic` (`exam_subject_id`,`topic_id`),
  KEY `idx_exam_subject_topics_exam_subject_id` (`exam_subject_id`),
  KEY `idx_exam_subject_topics_topic_id` (`topic_id`),
  KEY `idx_exam_subject_topics_is_active` (`is_active`),
  CONSTRAINT `fk_exam_subject_topics_exam_subject`
    FOREIGN KEY (`exam_subject_id`) REFERENCES `exam_subjects` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_exam_subject_topics_topic`
    FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `exam_subject_topics`;');
    }
};
