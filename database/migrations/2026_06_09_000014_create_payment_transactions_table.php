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
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `gateway` enum('mercado_pago','pagseguro') NOT NULL DEFAULT 'mercado_pago',
  `external_id` varchar(255) DEFAULT NULL,
  `provider_payment_id` varchar(120) DEFAULT NULL,
  `gateway_reference` varchar(190) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','failed','refunded','canceled') NOT NULL DEFAULT 'pending',
  `status_detail` varchar(190) DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payment_transactions_user_id` (`user_id`),
  KEY `idx_payment_transactions_subscription_id` (`subscription_id`),
  KEY `idx_payment_transactions_status` (`status`),
  KEY `idx_payment_transactions_provider_payment_id` (`provider_payment_id`),
  KEY `idx_payment_transactions_gateway_reference` (`gateway_reference`),
  CONSTRAINT `fk_payment_transactions_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payment_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
