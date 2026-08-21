<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('acquisition_source', 120)->nullable()->after('last_login_at');
            $table->string('acquisition_medium', 120)->nullable()->after('acquisition_source');
            $table->string('acquisition_campaign', 190)->nullable()->after('acquisition_medium');
            $table->string('acquisition_content', 190)->nullable()->after('acquisition_campaign');
            $table->string('acquisition_term', 190)->nullable()->after('acquisition_content');
            $table->string('acquisition_landing_path', 500)->nullable()->after('acquisition_term');
            $table->string('acquisition_referrer', 1000)->nullable()->after('acquisition_landing_path');
            $table->timestamp('acquisition_captured_at')->nullable()->after('acquisition_referrer');

            $table->index('acquisition_source');
            $table->index('acquisition_campaign');
            $table->index('acquisition_captured_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['acquisition_source']);
            $table->dropIndex(['acquisition_campaign']);
            $table->dropIndex(['acquisition_captured_at']);

            $table->dropColumn([
                'acquisition_source',
                'acquisition_medium',
                'acquisition_campaign',
                'acquisition_content',
                'acquisition_term',
                'acquisition_landing_path',
                'acquisition_referrer',
                'acquisition_captured_at',
            ]);
        });
    }
};
