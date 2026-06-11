<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration documental. Não executar em produção sem revisar.
     * A estrutura atual de users já possui o enum de roles usado no CRUD de colaboradores.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` enum('student','admin','moderator','finance','marketing','content') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` enum('student','admin') NOT NULL DEFAULT 'student'");
    }
};
