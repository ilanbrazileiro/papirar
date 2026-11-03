<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['questions', 'exams', 'courses'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('education_level', 10)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['questions', 'exams', 'courses'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['education_level']);
                $blueprint->dropColumn('education_level');
            });
        }
    }
};
