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
CREATE TABLE IF NOT EXISTS `user_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `study_session_id` bigint(20) unsigned DEFAULT NULL,
  `selected_alternative_id` bigint(20) unsigned DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_answers_user_id` (`user_id`),
  KEY `idx_user_answers_question_id` (`question_id`),
  KEY `idx_user_answers_session_id` (`study_session_id`),
  KEY `fk_user_answers_alternative` (`selected_alternative_id`),
  CONSTRAINT `fk_user_answers_alternative` FOREIGN KEY (`selected_alternative_id`) REFERENCES `alternatives` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_answers_session` FOREIGN KEY (`study_session_id`) REFERENCES `study_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_answers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
    }
};
