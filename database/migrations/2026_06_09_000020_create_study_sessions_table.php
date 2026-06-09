<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `study_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `corporation_id` bigint(20) unsigned DEFAULT NULL,
  `exam_id` bigint(20) unsigned DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `mode` enum('train','exam') NOT NULL DEFAULT 'train',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_study_sessions_user_id` (`user_id`),
  KEY `idx_study_sessions_mode` (`mode`),
  KEY `fk_study_sessions_corporation` (`corporation_id`),
  KEY `fk_study_sessions_exam` (`exam_id`),
  KEY `fk_study_sessions_subject` (`subject_id`),
  KEY `fk_study_sessions_topic` (`topic_id`),
  CONSTRAINT `fk_study_sessions_corporation` FOREIGN KEY (`corporation_id`) REFERENCES `corporations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_study_sessions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_study_sessions_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_study_sessions_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_study_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_sessions');
    }
};
