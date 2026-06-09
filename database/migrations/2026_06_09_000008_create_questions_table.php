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
CREATE TABLE IF NOT EXISTS `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corporation_id` bigint(20) unsigned DEFAULT NULL,
  `exam_id` bigint(20) unsigned DEFAULT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `statement` longtext NOT NULL,
  `question_type` enum('multiple_choice','true_false') NOT NULL DEFAULT 'multiple_choice',
  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'medium',
  `source_type` enum('exam','authored','adapted') NOT NULL DEFAULT 'authored',
  `source_reference` varchar(255) DEFAULT NULL,
  `commented_answer` longtext DEFAULT NULL,
  `status` enum('draft','review','published','archived') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_questions_corporation_id` (`corporation_id`),
  KEY `idx_questions_exam_id` (`exam_id`),
  KEY `idx_questions_subject_id` (`subject_id`),
  KEY `idx_questions_topic_id` (`topic_id`),
  KEY `idx_questions_difficulty` (`difficulty`),
  KEY `idx_questions_source_type` (`source_type`),
  KEY `idx_questions_status` (`status`),
  KEY `idx_questions_created_by` (`created_by`),
  CONSTRAINT `fk_questions_corporation` FOREIGN KEY (`corporation_id`) REFERENCES `corporations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_questions_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
