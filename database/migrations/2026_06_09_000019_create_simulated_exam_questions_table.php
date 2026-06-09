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
CREATE TABLE IF NOT EXISTS `simulated_exam_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `simulated_exam_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `selected_alternative_id` bigint(20) unsigned DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_simulated_exam_question` (`simulated_exam_id`,`question_id`),
  UNIQUE KEY `uq_simulated_exam_position` (`simulated_exam_id`,`position`),
  KEY `fk_simulated_exam_questions_question` (`question_id`),
  KEY `fk_simulated_exam_questions_alternative` (`selected_alternative_id`),
  CONSTRAINT `fk_simulated_exam_questions_alternative` FOREIGN KEY (`selected_alternative_id`) REFERENCES `alternatives` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_simulated_exam_questions_exam` FOREIGN KEY (`simulated_exam_id`) REFERENCES `simulated_exams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_simulated_exam_questions_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulated_exam_questions');
    }
};
