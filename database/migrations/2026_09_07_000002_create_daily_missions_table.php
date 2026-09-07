<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('mission_date');
            $table->string('code', 50);
            $table->unsignedSmallInteger('target');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'mission_date', 'code'], 'daily_missions_user_date_code_unique');
            $table->index(['user_id', 'mission_date'], 'daily_missions_user_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_missions');
    }
};
