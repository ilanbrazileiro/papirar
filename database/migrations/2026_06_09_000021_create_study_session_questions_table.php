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
CREATE TABLE IF NOT EXISTS `study_session_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `study_session_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_study_session_questions_session_position` (`study_session_id`,`position`),
  UNIQUE KEY `uq_study_session_questions_session_question` (`study_session_id`,`question_id`),
  KEY `idx_study_session_questions_question_id` (`question_id`),
  CONSTRAINT `fk_study_session_questions_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_study_session_questions_session` FOREIGN KEY (`study_session_id`) REFERENCES `study_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_session_questions');
    }
};
