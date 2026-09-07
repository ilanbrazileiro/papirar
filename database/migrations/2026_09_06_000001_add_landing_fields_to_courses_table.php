<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('landing_enabled')->default(false)->after('is_public')->index();
            $table->string('landing_headline', 180)->nullable()->after('landing_enabled');
            $table->string('landing_subheadline', 500)->nullable()->after('landing_headline');
            $table->string('landing_problem_title', 180)->nullable()->after('landing_subheadline');
            $table->text('landing_problem_text')->nullable()->after('landing_problem_title');
            $table->string('landing_cta_text', 80)->nullable()->after('landing_problem_text');
            $table->string('landing_final_title', 180)->nullable()->after('landing_cta_text');
            $table->string('landing_final_text', 500)->nullable()->after('landing_final_title');
            $table->string('landing_seo_title', 70)->nullable()->after('landing_final_text');
            $table->string('landing_seo_description', 170)->nullable()->after('landing_seo_title');
            $table->unsignedBigInteger('landing_question_id')->nullable()->after('landing_seo_description')->index();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['landing_enabled']);
            $table->dropIndex(['landing_question_id']);
            $table->dropColumn([
                'landing_enabled', 'landing_headline', 'landing_subheadline',
                'landing_problem_title', 'landing_problem_text', 'landing_cta_text',
                'landing_final_title', 'landing_final_text', 'landing_seo_title',
                'landing_seo_description', 'landing_question_id',
            ]);
        });
    }
};
