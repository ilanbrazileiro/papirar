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
CREATE TABLE IF NOT EXISTS `exams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corporation_id` bigint(20) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `year` int(11) NOT NULL,
  `exam_type` varchar(50) NOT NULL,
  `status` enum('planned','published') NOT NULL DEFAULT 'published',
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_exams_corporation_id` (`corporation_id`),
  KEY `idx_exams_year` (`year`),
  KEY `idx_exams_exam_type` (`exam_type`),
  KEY `idx_exams_active` (`active`),
  CONSTRAINT `fk_exams_corporation` FOREIGN KEY (`corporation_id`) REFERENCES `corporations` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
