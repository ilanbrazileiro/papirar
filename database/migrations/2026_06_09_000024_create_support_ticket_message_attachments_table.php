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
CREATE TABLE IF NOT EXISTS `support_ticket_message_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_message_id` bigint(20) unsigned NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `extension` varchar(20) DEFAULT NULL,
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ticket_message_attachments_message` (`support_ticket_message_id`),
  CONSTRAINT `fk_ticket_message_attachments_message` FOREIGN KEY (`support_ticket_message_id`) REFERENCES `support_ticket_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_message_attachments');
    }
};
