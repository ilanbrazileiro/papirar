<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('landing_show_performance')->default(false)->after('landing_question_id');
            $table->boolean('landing_show_error_review')->default(false)->after('landing_show_performance');
            $table->boolean('landing_show_next_study')->default(false)->after('landing_show_error_review');
            $table->boolean('landing_show_schedule')->default(false)->after('landing_show_next_study');
            $table->boolean('landing_show_goals')->default(false)->after('landing_show_schedule');
            $table->boolean('landing_show_simulations')->default(false)->after('landing_show_goals');
            $table->string('landing_performance_image_path')->nullable()->after('landing_show_simulations');
            $table->string('landing_final_cta_text', 80)->nullable()->after('landing_performance_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'landing_show_performance',
                'landing_show_error_review',
                'landing_show_next_study',
                'landing_show_schedule',
                'landing_show_goals',
                'landing_show_simulations',
                'landing_performance_image_path',
                'landing_final_cta_text',
            ]);
        });
    }
};
