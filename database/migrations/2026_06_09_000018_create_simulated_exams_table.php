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
CREATE TABLE IF NOT EXISTS `simulated_exams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(180) DEFAULT NULL,
  `corporation_id` bigint(20) unsigned DEFAULT NULL,
  `exam_id` bigint(20) unsigned DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `topic_id` bigint(20) unsigned DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT NULL,
  `source_type` enum('exam','authorial') DEFAULT NULL,
  `total_questions` int(10) unsigned NOT NULL DEFAULT 0,
  `correct_answers` int(10) unsigned NOT NULL DEFAULT 0,
  `accuracy` decimal(5,2) NOT NULL DEFAULT 0.00,
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_simulated_exams_user_id` (`user_id`),
  KEY `fk_simulated_exams_corporation` (`corporation_id`),
  KEY `fk_simulated_exams_exam` (`exam_id`),
  KEY `fk_simulated_exams_subject` (`subject_id`),
  KEY `fk_simulated_exams_topic` (`topic_id`),
  CONSTRAINT `fk_simulated_exams_corporation` FOREIGN KEY (`corporation_id`) REFERENCES `corporations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_simulated_exams_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_simulated_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_simulated_exams_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_simulated_exams_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulated_exams');
    }
};
